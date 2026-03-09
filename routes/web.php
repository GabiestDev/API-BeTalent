<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Load API routes under '/api' prefix when present
if (file_exists(__DIR__.'/api.php')) {
    Route::prefix('api')->group(function () {
        require __DIR__.'/api.php';
    });
}
