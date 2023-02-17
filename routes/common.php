<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Common\RoleController;
use App\Http\Controllers\Common\UserController;
use App\Http\Controllers\Common\StoreController;
use App\Http\Controllers\Common\SubMenuController;

Route::resource('roles', RoleController::class);
Route::resource('users', UserController::class);
Route::post('user-status', [UserController::class, 'updateStatus'])->name('userStatus');
Route::resource('stores', StoreController::class);
// profile
Route::get('changepassword', [UserController::class, 'changepassword'])->name('changepassword');
Route::post('updatepassword', [UserController::class, 'updatepassword']);
Route::get('ban/{id}', [UserController::class, 'ban'])->name('ban');
Route::get('sub-menu/{id}', [SubMenuController::class, 'subMenuList']);

