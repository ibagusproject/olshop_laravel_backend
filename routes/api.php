<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\ProductGalleryController;
use App\Http\Controllers\API\ProductCategoryController;

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
Route::get('galleries', [ProductGalleryController::class, 'all']);

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [UserController::class, 'fetch']);
    Route::match(['put', 'patch'], 'user', [UserController::class, 'updateProfile']);
    Route::post('logout', [UserController::class, 'logout']);

    // transaction
    Route::get('transaction', [TransactionController::class, 'all']);

    // checkout
    Route::post('checkout', [TransactionController::class, 'checkout']);

    // admin
    Route::middleware('is_admin')->group(function () {
        Route::get('users', [UserController::class, 'all']);
        Route::post('products', [ProductController::class, 'store']);
        Route::match(['put', 'patch'], 'products', [ProductController::class, 'update']);

        Route::post('categories', [ProductCategoryController::class, 'store']);
        Route::match(['put', 'patch'], 'categories', [ProductCategoryController::class, 'update']);

        Route::post('galleries', [ProductGalleryController::class, 'store']);
        Route::match(['put', 'patch'], 'galleries', [ProductGalleryController::class, 'update']);
    });
});
