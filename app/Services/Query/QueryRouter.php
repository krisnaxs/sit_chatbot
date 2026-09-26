<?php

namespace App\Services\Query;

class QueryRouter
{
    /**
     * Coba jawab dari semua service DB.
     * Return array [jawaban, sumber] atau null.
     */
    public function tryAnswer(string $pesan): ?array
    {
        $services = [
            app(CrossQueryService::class),
            app(AnalyticsQueryService::class),
            app(SiamQueryService::class),
            app(AssetQueryService::class),
            app(UserQueryService::class),
            app(LocationQueryService::class),
            app(VendorQueryService::class),
            app(ReportQueryService::class),
        ];

        foreach ($services as $service) {
            $result = $service->tryAnswer($pesan);
            if ($result) {
                return $result;
            }
        }

        return null;
    }
}
