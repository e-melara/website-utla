<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index');
// Route::resource('/login', 'LoginController');

// routes for routes
Route::get('/pdf/matriculas', 'PdfController@all');
Route::get('/pdf/matricula/{id}', 'PdfController@matricula');
// pago
Route::get('/pdf/pago/{id}', 'PdfController@pago');