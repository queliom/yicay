<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TransferController;

Route::get('/ping', function() {
    return ['pong' => true];
});

Route::post('/transfer', [TransferController::class, 'create']);
