<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});
