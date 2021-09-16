<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentEnrolledSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("student_enrolled_subjects", function (
            Blueprint $table
        ) {
            $table->id();
            $table->integer("codcarga");
            $table->unsignedBigInteger("student_enrolled_id");
            $table->enum("estado", ["I", "A", "D"])->default("I");
            $table
                ->foreign("student_enrolled_id")
                ->references("id")
                ->on("student_enrolleds");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("student_enrolled_subjects");
    }
}
