<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsEstadoCodcarga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('solicitudes_cargas_academicas', function (Blueprint $table) {
            $table->enum('estado', ['I', 'A', 'D'])->default('I');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('solicitudes_cargas_academicas', function (Blueprint $table) {
            //
        });
    }
}
