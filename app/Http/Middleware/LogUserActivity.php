<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Method HTTP yang otomatis dicatat.
     */
    protected array $logMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Route yang TIDAK dicatat (biar tidak spam / tidak relevan / sudah di-handle observer).
     */
    protected array $skipRoutes = [
        // ============================================================
        // Auth & Chatbot
        // ============================================================
        'admin.login',
        'admin.logout',
        'chat.send',

        // ============================================================
        // Activity Log sendiri (biar tidak loop / tidak spam)
        // ============================================================
        'activity.index',
        'activity.show',
        'activity.clear',
        'activity.destroy',

        // ============================================================
        // 🆕 Route yang SUDAH di-handle Observer Global
        // Skip biar tidak dobel log
        // ============================================================

        // App
        'apps.store',
        'apps.update',
        'apps.destroy',

        // User
        'users.store',
        'users.update',
        'users.destroy',

        // Knowledge
        'knowledge.store',
        'knowledge.update',
        'knowledge.destroy',
        'knowledge.pending.approve',
        'knowledge.pending.reject',
        'knowledge.pending.clear',

        // SIAM — Aset
        'siam.assets.store',
        'siam.assets.update',
        'siam.assets.destroy',

        // SIAM — Assignment
        'siam.assignments.store',
        'siam.assignments.return',
        'siam.assignments.destroy',

        // SIAM — Loan
        'siam.loans.store',
        'siam.loans.return',
        'siam.loans.destroy',

        // SIAM — Maintenance
        'siam.maintenances.store',
        'siam.maintenances.update',
        'siam.maintenances.destroy',

        // SIAM — Category
        'siam.categories.store',
        'siam.categories.update',
        'siam.categories.destroy',

        // SIAM — Asset Type (Brand & Model)
        'siam.asset-types.store',
        'siam.asset-types.update',
        'siam.asset-types.destroy',

        // SIAM — Consumable
        'siam.consumables.store',
        'siam.consumables.update',
        'siam.consumables.destroy',

        // SIAM — Consumable Transaction
        'siam.consumable-transactions.store',
        'siam.consumable-transactions.destroy',

        // SIAM — Vendor
        'siam.vendors.store',
        'siam.vendors.update',
        'siam.vendors.destroy',

        // SIAM — Department
        'siam.departments.store',
        'siam.departments.update',
        'siam.departments.destroy',

        // SIAM — Location
        'siam.locations.store',
        'siam.locations.update',
        'siam.locations.destroy',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldLog($request, $response)) {
            return $response;
        }

        $this->log($request, $response);

        return $response;
    }

    protected function shouldLog(Request $request, Response $response): bool
    {
        if (!auth()->check()) {
            return false;
        }

        if (!in_array($request->method(), $this->logMethods, true)) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, $this->skipRoutes, true)) {
            return false;
        }

        return true;
    }

    protected function log(Request $request, Response $response): void
    {
        $user = auth()->user();
        $routeName = $request->route()?->getName() ?? $request->path();

        $logName = $this->resolveLogName($routeName);
        $event = $this->resolveEvent($routeName, $request->method());
        $description = $this->buildDescription($user, $event, $routeName);

        activity($logName)
            ->causedBy($user)
            ->event($event)
            ->withProperties([
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $routeName,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $response->getStatusCode(),
                'input' => $this->sanitizeInput($request->except([
                    '_token',
                    '_method',
                ])),
            ])
            ->log($description);
    }

    /**
     * Kelompokkan log_name berdasarkan prefix route.
     */
    protected function resolveLogName(string $routeName): string
    {
        return match (true) {
            str_starts_with($routeName, 'siam.') => 'siam',
            str_starts_with($routeName, 'users.') => 'user',
            str_starts_with($routeName, 'knowledge.') => 'knowledge',
            str_starts_with($routeName, 'apps.') => 'app',
            str_starts_with($routeName, 'activity.') => 'activity',
            default => 'app',
        };
    }

    /**
     * Tentukan event berdasarkan method + nama route.
     */
    protected function resolveEvent(string $routeName, string $method): string
    {
        // Event khusus berdasarkan suffix route
        if (str_ends_with($routeName, '.return') || str_ends_with($routeName, '.returnAsset')) {
            return 'returned';
        }
        if (str_ends_with($routeName, '.approve')) {
            return 'approved';
        }
        if (str_ends_with($routeName, '.reject')) {
            return 'rejected';
        }
        if (str_ends_with($routeName, '.clear')) {
            return 'cleared';
        }
        if (str_ends_with($routeName, '.click')) {
            return 'clicked';
        }

        // Default by HTTP method
        return match ($method) {
            'POST' => 'created',
            'PUT',
            'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => 'accessed',
        };
    }

    /**
     * Bikin deskripsi human-readable: "Budi menambah Aset".
     */
    protected function buildDescription($user, string $event, string $routeName): string
    {
        $verb = match ($event) {
            'created' => 'menambah',
            'updated' => 'mengubah',
            'deleted' => 'menghapus',
            'returned' => 'mengembalikan',
            'approved' => 'menyetujui',
            'rejected' => 'menolak',
            'cleared' => 'membersihkan',
            'clicked' => 'mengklik',
            default => 'mengakses',
        };

        $resource = $this->humanizeRoute($routeName);

        return "{$user->name} {$verb} {$resource}";
    }

    /**
     * Mapping resource dari nama route ke label Indonesia.
     */
    protected function humanizeRoute(string $routeName): string
    {
        $parts = explode('.', $routeName);

        $resource = count($parts) >= 2
            ? $parts[count($parts) - 2]
            : $parts[0];

        $map = [
            // SIAM
            'assets' => 'Aset',
            'asset-types' => 'Brand & Model',
            'categories' => 'Kategori',
            'consumables' => 'Konsumable',
            'consumable-transactions' => 'Transaksi Konsumable',
            'assignments' => 'Serah Terima',
            'loans' => 'Peminjaman',
            'maintenances' => 'Perbaikan',
            'vendors' => 'Vendor',
            'departments' => 'Departemen',
            'locations' => 'Lokasi',

            // User & App
            'users' => 'User',
            'apps' => 'Aplikasi',

            // Knowledge
            'knowledge' => 'Knowledge',
            'pending' => 'Pending Knowledge',

            // Activity
            'activity' => 'Activity Log',
        ];

        return $map[$resource] ?? ucwords(str_replace('-', ' ', $resource));
    }

    /**
     * Buang field sensitif dari input.
     */
    protected function sanitizeInput(array $input): array
    {
        $sensitive = [
            'password',
            'password_confirmation',
            'token',
            'secret',
            'api_key',
            '_token',
            '_method',
        ];

        foreach ($sensitive as $key) {
            if (array_key_exists($key, $input)) {
                $input[$key] = '***';
            }
        }

        return $input;
    }
}
