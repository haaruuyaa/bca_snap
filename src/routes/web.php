<?php

use Haaruuyaa\BcaSnap\Controllers\BcaSnapController;
use Illuminate\Support\Facades\Route;



Route::prefix('bca-api')->group(function () {
    Route::get('balance', [BcaSnapController::class,'getBalance']);
});
