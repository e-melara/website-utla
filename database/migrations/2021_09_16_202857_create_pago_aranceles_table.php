<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagoArancelesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('pago_aranceles', function (Blueprint $table) {
      $table->unsignedBigInteger('pago_id');
      $table->string('arancel_id', 12);
      $table->float('precio');

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
    Schema::dropIfExists('pago_aranceles');
  }
}
