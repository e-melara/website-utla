<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('pagos', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('student_enrolled_id');

      $table->unsignedBigInteger('banco_id');

      $table->string('nombre_titular', 50);
      $table->tinyInteger('is_titular')->default(1);
      $table->float('monto');
      $table->date('fecha_pago');
      $table->mediumText('concepto');
      $table->timestamps();

      $table->foreign('banco_id')
        ->references('id')
        ->on('bancos');

      $table
        ->foreign('student_enrolled_id')
        ->references('id')
        ->on('student_enrolleds');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('pagos');
  }
}
