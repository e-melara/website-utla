<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulosTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('modulos', function (Blueprint $table) {
      $table->id();
      $table->string('nombre'); // El dato que va aparecer en el sidebar
      $table->string('icon', 20);
      $table->string('short_name', 20); // El dato de la ruta
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('modulos');
  }
}
