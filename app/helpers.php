<?php
// app/helpers.php (create if doesn't exist, then add to composer.json autoload.files)

use App\Services\ActivityLogService;

if (!function_exists('activity')) {
    function activity(?string $logName = null): ActivityLogService
    {
        $service = app(ActivityLogService::class);
        
        return $service;
    }
}