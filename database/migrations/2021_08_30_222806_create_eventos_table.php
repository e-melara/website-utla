<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventosTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('eventos', function (Blueprint $table) {
      $table->id();
      $table->date('begin_date');
      $table->string('title', 100);
      $table->string('month_year', 8);
      $table->date('end_date')->nullable();
      $table->boolean('is_end_date')->default(true);
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
    Schema::dropIfExists('eventos');
  }
}
