<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request ) {
    return ->user();
});

// Supplier API
Route::post('/suppliers', [App\Http\Controllers\SupplierController::class, 'store']);
