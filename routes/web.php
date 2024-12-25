<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiDataController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/fetch-sales', [ApiDataController::class, 'fetchSales']);
Route::get('/fetch-incomes', [ApiDataController::class, 'fetchIncomes']);
Route::get('/fetch-stocks', [ApiDataController::class, 'fetchStocks']);
Route::get('/fetch-orders', [ApiDataController::class, 'fetchOrders']);
Route::get('/daily-update', [ApiDataController::class, 'dailyUpdate']);