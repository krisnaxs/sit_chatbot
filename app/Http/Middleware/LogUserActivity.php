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
        'admin.login',
        'admin.logout',
        'chat.send',
        'activity.index',
        'activity.show',
        'activity.clear',
        'activity.destroy',
        'apps.store',
        'apps.update',
        'apps.destroy',
        'users.store',
        'users.update',
        'users.destroy',
        'knowledge.store',
        'knowledge.update',
        'knowledge.destroy',
        'knowledge.pending.approve',
        'knowledge.pending.reject',
        'knowledge.pending.clear',
        'siam.assets.store',
        'siam.assets.update',
        'siam.assets.destroy',
        'siam.assignments.store',
        'siam.assignments.return',
        'siam.assignments.destroy',
        'siam.loans.store',
        'siam.loans.return',
        'siam.loans.destroy',
        'siam.maintenances.store',
        'siam.maintenances.update',
        'siam.maintenances.destroy',
        'siam.categories.store',
        'siam.categories.update',
        'siam.categories.destroy',
        'siam.asset-types.store',
        'siam.asset-types.update',
        'siam.asset-types.destroy',
        'siam.consumables.store',
        'siam.consumables.update',
        'siam.consumables.destroy',
        'siam.consumable-transactions.store',
        'siam.consumable-transactions.destroy',
        'siam.vendors.store',
        'siam.vendors.update',
        'siam.vendors.destroy',
        'siam.departments.store',
        'siam.departments.update',
        'siam.departments.destroy',
        'siam.locations.store',
        'siam.locations.update',
        'siam.locations.destroy',
        'requests.store',
        'requests.cancel',
        'requests.quick.store',
        'admin.requests.approve',
        'admin.requests.reject',
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
            str_starts_with($routeName, 'requests.') => 'request',           //
            str_starts_with($routeName, 'admin.requests.') => 'request',     //
            default => 'app',
        };
    }

    /**
     * Tentukan event berdasarkan method + nama route.
     */
    protected function resolveEvent(string $routeName, string $method): string
    {
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
            'users' => 'User',
            'apps' => 'Aplikasi',
            'knowledge' => 'Knowledge',
            'pending' => 'Pending Knowledge',
            'activity' => 'Activity Log',
            'requests' => 'Requests',           //
            'quick' => 'Quick Request',         //
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
