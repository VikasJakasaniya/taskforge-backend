<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('imports.{userId}', function ($user, $userId) {

    if (!$user) {
        Log::error('❌❌❌ USER IS NULL - SANCTUM FAILED');
        return false;
    }

    return (int) $user->id === (int) $userId;
});
