<?php

use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('products', [ProductController::class, 'all']);
Route::get('categories', [ProductCategoryController::class, 'all']);

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [UserController::class, 'fetch']);
    Route::patch('user', [UserController::class, 'updateProfile']);
    Route::post('logout', [UserController::class, 'logout']);

    // transaction
    Route::get('transaction', [TransactionController::class, 'all']);

    // checkout
    Route::post('checkout', [TransactionController::class, 'checkout']);

    // admin
    Route::middleware('is_admin')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::patch('products', [ProductController::class, 'update']);

        Route::post('categories', [ProductCategoryController::class, 'store']);
        Route::patch('categories', [ProductCategoryController::class, 'update']);
    });
});
