<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\UserController;
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

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::resource('users', UserController::class);
    Route::resource('books', BookController::class);
    Route::get('users/books/{id}', [BookController::class, 'getRentedBooks']);
    Route::post('users/books/issue', [BookController::class, 'issueBook']);
    Route::patch('users/books/return/{id}', [BookController::class, 'returnBook']);
});