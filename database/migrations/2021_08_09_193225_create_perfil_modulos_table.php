<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerfilModulosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("perfil_modulos", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("perfil_id");

            $table->enum("add", ["A", "I"])->default("I");
            $table->enum("update", ["A", "I"])->default("I");
            $table->enum("delete", ["A", "I"])->default("I");
            $table->enum("view", ["A", "I"])->default("A");

            $table
                ->foreign("perfil_id")
                ->references("id")
                ->on("perfils");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("perfil_modulos");
    }
}
