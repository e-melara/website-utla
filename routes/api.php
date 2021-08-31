<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function() {
  Route::post('login', 'API\AuthController@login');
  Route::middleware('token')->group(function() {
    Route::post("me", "API\AuthController@me");
  });
});

Route::middleware('token')->group(function() {
  Route::prefix('asesoria')->group(function() {
    Route::get('/me', 'API\AsesoriaController@asesoria');
    Route::get('/pensum', 'API\AsesoriaController@pensum');
    Route::post('/horario', 'API\AsesoriaController@getHorarioSubject');
    Route::get('/enrolled', 'API\AsesoriaController@getEnrolledSubject');
    Route::post('/registro', 'API\AsesoriaController@saveRegistroSubject');
  });

  Route::prefix('notes')->group(function() {
    Route::get('/me', 'API\NotesController@me');
  });

  Route::prefix('solicitud')->group(function() {
    Route::post('/add', 'API\SolicitudController@add');
    Route::get('/', 'API\SolicitudController@paginator');
  });

  Route::prefix('eventos')->group(function() {
    Route::resource('/', 'API\EventosController');
  });
});