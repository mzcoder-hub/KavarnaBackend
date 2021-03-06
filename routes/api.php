<?php

use App\Http\Controllers\API\MenuCategoryController;
use App\Http\Controllers\API\MenuController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('menus', [MenuController::class, 'all']);
Route::get('transaction/queue', [TransactionController::class, 'transactionQueue']);
Route::get('categories', [MenuCategoryController::class, 'all']);
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::delete('user/delete', [UserController::class, 'delete']);
Route::get('user/all', [UserController::class, 'fetchAll']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('logout', [UserController::class, 'logout']);

    Route::post('menus', [MenuController::class, 'store']);
    Route::post('menus/update', [MenuController::class, 'update']);
    Route::delete('menus', [MenuController::class, 'delete']);

    Route::get('transaction', [TransactionController::class, 'all']);
    Route::post('transaction/change', [TransactionController::class, 'changeStatus']);

    Route::get('transaction/range', [TransactionController::class, 'transactionRange']);

    Route::post('checkout', [TransactionController::class, 'checkout']);
    Route::post('categories/update', [MenuCategoryController::class, 'update']);
    Route::post('categories', [MenuCategoryController::class, 'store']);
    Route::delete('categories/delete', [MenuCategoryController::class, 'delete']);
});
