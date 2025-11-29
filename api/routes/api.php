<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

require __DIR__.'/api/users.php';
require __DIR__.'/api/teams.php';
require __DIR__.'/api/chat.php';
