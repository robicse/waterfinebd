<?php
use Illuminate\Support\Facades\Route;



Route::group(['as'=>'superadmin.','prefix' =>'super-admin', 'middleware' => ['auth', 'superadmin']], function(){

});

