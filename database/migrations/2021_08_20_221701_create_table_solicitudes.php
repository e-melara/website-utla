<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSolicitudes extends Migration
{
  public function up()
  {
    Schema::create('solicitudes', function (Blueprint $table) {
      $table->id();
      $table->enum('type', ['SEXTA', 'TUTORIADA', 'SUFICIENCIA']);
      $table->string('ciclo', 10);
      $table->string('carnet', 20);
      $table->text('observacion');
      $table->string('codmate', 10);
      $table->enum('estado', ['I', 'A', 'D'])->default('I');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('solicitudes');
  }
}
