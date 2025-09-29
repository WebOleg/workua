<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RedirectController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/{shortCode}', [RedirectController::class, 'redirect'])
    ->where('shortCode', '[a-zA-Z0-9]{6,10}');
