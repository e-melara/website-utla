<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagoArchivosTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('pago_archivos', function (Blueprint $table) {
      $table->unsignedBigInteger('pago_id');
      $table->string('url', 70);
      $table->string('tipo', 10);

      $table
        ->foreign('pago_id')
        ->references('id')
        ->on('pagos');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('pago_archivos');
  }
}
