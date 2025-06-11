<?php
// routes/api.php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/videos')->group(function () {
    Route::post('/', [VideoController::class, 'store']);
    Route::get('/', [VideoController::class, 'index']);
    Route::get('/{id}', [VideoController::class, 'show']);
});
