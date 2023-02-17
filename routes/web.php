<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserLoginController;

// landing page
Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();
// phone or email login
Route::post('/login', [UserLoginController::class, 'login'])->name('user.login');
Route::post('/logout', [UserLoginController::class, 'logout'])->name('logout');


