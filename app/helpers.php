<?php

use App\Services\ActivityLogService;
use App\Models\User;

if (!function_exists('activity')) {
    function activity(?string $logName = null): ActivityLogService
    {
        $service = app(ActivityLogService::class);
        
        return $service;
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }
}