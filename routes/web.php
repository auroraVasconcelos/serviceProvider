<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [OAuthController::class, 'redirect'])->name('login');
Route::get('/callback', [OAuthController::class, 'callback'])->name('callback');
