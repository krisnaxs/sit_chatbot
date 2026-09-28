<?php

namespace App\Services\Query;

use Illuminate\Support\Facades\DB;

class QueryRouter
{
    /**
     * Coba jawab dari semua service DB.
     * Return array [jawaban, sumber] atau null.
     */
    public function tryAnswer(string $pesan): ?array
    {
        $services = [
            app(AssetQueryService::class),
            app(CrossQueryService::class),
            app(AnalyticsQueryService::class),
            app(SiamQueryService::class),
            app(UserQueryService::class),
            app(LocationQueryService::class),
            app(VendorQueryService::class),
            app(ReportQueryService::class),
            app(ActivityQueryService::class),
        ];

        // 🆕 Inject user ke service yang butuh
        $user = auth()->user();
        foreach ($services as $service) {
            if (method_exists($service, 'setUser')) {
                $service->setUser($user);
            }
        }

        // 🆕 Paksa koneksi read-only selama query service AI berjalan
        $original = DB::getDefaultConnection();
        DB::setDefaultConnection('ai_readonly');

        try {
            foreach ($services as $service) {
                $result = $service->tryAnswer($pesan);
                if ($result) {
                    return $result;
                }
            }
        } finally {
            // Selalu restore — apapun yang terjadi
            DB::setDefaultConnection($original);
        }

        return null;
    }
}
