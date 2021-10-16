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


  Route::get('/config', 'API\Admin\AsesoriaAdminController@configuracion');
  Route::post('/config', 'API\Admin\AsesoriaAdminController@configuracionSave');

  // Routes for administrador
  Route::prefix('admin')->group(function () {
    Route::prefix('/asesoria')->group(function () {
      Route::get('/', 'API\Admin\AsesoriaAdminController@all');
      Route::get('/{id}', 'API\Admin\AsesoriaAdminController@getById');
      Route::post('/', 'API\Admin\AsesoriaAdminController@changeStatus');
      Route::post('/enrolled', 'API\Admin\AsesoriaAdminController@enrolled');
    });

    Route::prefix('/solicitudes')->group(function() {
      Route::get('/', 'API\Admin\SolicitudesController@all');
      Route::get('/{id}', 'API\Admin\SolicitudesController@findById');
      Route::post('/', 'API\Admin\SolicitudesController@save');
    });

    // perfiles
    Route::prefix('/perfiles')->group(function() {
      Route::get('/', 'API\Admin\PerfilesController@all');
      
      Route::post('/add', 'API\Admin\PerfilesController@add');
      Route::post('/new-perfil', 'API\Admin\PerfilesController@newPerfil');
      Route::post('/update-perfil', 'API\Admin\PerfilesController@updatePerfil');
      Route::post('/delete-perfil', 'API\Admin\PerfilesController@deletePerfil');
      Route::post('/delete-modulo', 'API\Admin\PerfilesController@eliminarModulo');
      Route::get('/{id}', 'API\Admin\PerfilesController@findById');
    });

    // users
    // route: admin/users/*.*
    Route::prefix('/users')->group(function() {
      Route::get('/all', 'API\Admin\UsersController@all');
      Route::post('/perfil', 'API\Admin\UsersController@perfil');
      Route::post('/new-user', 'API\Admin\UsersController@save');
      Route::post('/password', 'API\Admin\UsersController@password');
      Route::post('/darbaja', 'API\Admin\UsersController@darBajarUser');
      Route::post('/name-last', 'API\Admin\UsersController@nameLastChange');

      // validate user
      Route::post('/validate-user', 'API\Admin\UsersController@validateUsername');
    });
  });
});
