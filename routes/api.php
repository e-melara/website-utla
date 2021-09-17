<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
  Route::post('login', 'API\AuthController@login');
  Route::middleware('token')->group(function () {
    Route::post('me', 'API\AuthController@me');
  });
});

Route::middleware('token')->group(function () {
  Route::prefix('asesoria')->group(function () {
    Route::get('/me', 'API\AsesoriaController@asesoria');
    Route::get('/pensum', 'API\AsesoriaController@pensum');
    Route::post('/horario', 'API\AsesoriaController@getHorarioSubject');
    Route::get('/enrolled', 'API\AsesoriaController@getEnrolledSubject');
    Route::post('/registro', 'API\AsesoriaController@saveRegistroSubject');

    // aranceles
    Route::post('/aranceles', 'API\Admin\AsesoriaAdminController@aranceles');
    Route::post('/aranceles/pago', 'API\Admin\AsesoriaAdminController@pagos');
  });

  Route::prefix('notes')->group(function () {
    Route::get('/me', 'API\NotesController@me');
  });

  Route::prefix('solicitud')->group(function () {
    Route::post('/add', 'API\SolicitudController@add');
    Route::get('/', 'API\SolicitudController@paginator');
    Route::get('/estadistica', 'API\SolicitudController@stadistic');
  });

  Route::prefix('eventos')->group(function () {
    Route::resource('/', 'API\EventosController');
  });

  // Routes for administrador
  Route::prefix('admin')->group(function () {
    Route::prefix('/asesoria')->group(function () {
      Route::get('/', 'API\Admin\AsesoriaAdminController@all');
      Route::get('/{id}', 'API\Admin\AsesoriaAdminController@getById');
      Route::post('/', 'API\Admin\AsesoriaAdminController@changeStatus');
    });
  });
});
