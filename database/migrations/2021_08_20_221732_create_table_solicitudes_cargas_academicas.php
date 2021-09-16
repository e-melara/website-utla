<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSolicitudesCargasAcademicas extends Migration
{
    public function up()
    {
        Schema::create("solicitudes_cargas_academicas", function (
            Blueprint $table
        ) {
            $table->id();
            $table->unsignedBigInteger("solicitud_id");
            $table->integer("codcarga");
            $table
                ->foreign("solicitud_id")
                ->references("id")
                ->on("solicitudes");
        });
    }
    public function down()
    {
        Schema::dropIfExists("solicitudes_cargas_academicas");
    }
}
