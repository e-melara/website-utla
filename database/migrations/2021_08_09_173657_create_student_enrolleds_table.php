<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentEnrolledsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('student_enrolleds', function (Blueprint $table) {
      $table->id();
      $table->string('ciclo', 10);
      $table->string('carnet', 15);
      $table->mediumText('observacion');
      $table->enum('estado', ['A', 'V', 'F', 'I', 'P']);
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
    Schema::dropIfExists('student_enrolleds');
  }
}
