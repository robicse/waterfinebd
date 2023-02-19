<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Common\RoleController;
use App\Http\Controllers\Common\UserController;
use App\Http\Controllers\Common\StoreController;
use App\Http\Controllers\Common\SubMenuController;
use App\Http\Controllers\Common\SupplierController;
use App\Http\Controllers\Common\CustomerController;
use App\Http\Controllers\Common\CategoryController;
use App\Http\Controllers\Common\ProductController;
use App\Http\Controllers\Common\PackageController;

Route::resource('roles', RoleController::class);
Route::resource('users', UserController::class);
Route::post('user-status', [UserController::class, 'updateStatus'])->name('userStatus');
Route::resource('stores', StoreController::class);
Route::get('changepassword', [UserController::class, 'changepassword'])->name('changepassword');
Route::post('updatepassword', [UserController::class, 'updatepassword']);
Route::get('ban/{id}', [UserController::class, 'ban'])->name('ban');
Route::get('sub-menu/{id}', [SubMenuController::class, 'subMenuList']);
Route::resource('suppliers', SupplierController::class);
Route::resource('customers', CustomerController::class);
Route::resource('categories', CategoryController::class);
Route::resource('products', ProductController::class);
Route::resource('packages', PackageController::class);
