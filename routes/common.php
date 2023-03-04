<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Common\CommonController;
use App\Http\Controllers\Common\RoleController;
use App\Http\Controllers\Common\UserController;
use App\Http\Controllers\Common\StoreController;
use App\Http\Controllers\Common\SubMenuController;
use App\Http\Controllers\Common\SupplierController;
use App\Http\Controllers\Common\CustomerController;
use App\Http\Controllers\Common\CategoryController;
use App\Http\Controllers\Common\ProductController;
use App\Http\Controllers\Common\PackageController;
use App\Http\Controllers\Common\PurchaseController;
use App\Http\Controllers\Common\StockController;
use App\Http\Controllers\Common\PurchaseReturnController;
use App\Http\Controllers\Common\SaleController;
use App\Http\Controllers\Common\SaleReturnController;
use App\Http\Controllers\Common\CustomerReceiptController;
use App\Http\Controllers\Common\CustomerDueController;
use App\Http\Controllers\Common\SupplierPaymentController;
use App\Http\Controllers\Common\SupplierDueController;

// common controller
Route::post('update-status', [CommonController::class, 'updateStatus'])->name('updateStatus');
Route::get('purchase-relation-data',[CommonController::class, 'PurchaseRelationData']);
Route::get('sale-relation-data',[CommonController::class, 'SaleRelationData']);
Route::post('find-product-info', [CommonController::class, 'FindProductInfo']);
Route::post('get-store-customer', [CommonController::class, 'FindCustomerInfo']);

Route::resource('roles', RoleController::class);
Route::resource('users', UserController::class);
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
Route::post('get-product-by-search', [PackageController::class, 'FindProductBySearchProductName']);
Route::get('/category-product-info', [PackageController::class, 'categoryProductInfo'])->name('category.product.info');
Route::resource('purchases', PurchaseController::class);
Route::resource('stocks', StockController::class);
Route::resource('purchase-returns', PurchaseReturnController::class);
Route::resource('sales', SaleController::class);
Route::get('sales-prints/{id}/{pagesize}', [SaleController::class, 'salePrintWithPageSize']);
Route::get('/sales-invoice-pdf/{id}', [SaleController::class, 'saleInvoicePdfDownload']);
Route::resource('sale-returns', SaleReturnController::class);
Route::resource('customer-receipts', CustomerReceiptController::class);
Route::get('customer-due-balance-info/{id}', [CustomerReceiptController::class, 'customerDueBalanceInfo']);
Route::resource('customer-dues', CustomerDueController::class);
Route::resource('supplier-payments', SupplierPaymentController::class);
Route::resource('supplier-dues', SupplierDueController::class);
Route::get('supplier-due-balance-info/{id}', [SupplierPaymentController::class, 'supplierDueBalanceInfo']);

