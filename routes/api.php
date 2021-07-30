<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function() {
  Route::post('login', 'API\AuthController@login');
  Route::middleware('token')->group(function() {
    Route::post("me", "API\AuthController@me");
    Route::post("logout", "API\AuthController@logout");
    Route::post("refresh", "API\AuthController@refresh");
  });
});
//Route::apiResource('asesoria', "API\AsesoriaController");