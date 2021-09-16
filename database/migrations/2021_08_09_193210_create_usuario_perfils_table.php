<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioPerfilsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("usuario_perfils", function (Blueprint $table) {
            $table->id();
            $table->string("usuario_id", 15);
            $table->unsignedBigInteger("perfil_id");

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
        Schema::dropIfExists("usuario_perfils");
    }
}
