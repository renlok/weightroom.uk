<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_exercises', function (Blueprint $table) {
            $table->increments('texercise_id');
            $table->string('texercise_name');
            $table->string('tmain_action');
            $table->string('tminor_action');
            $table->string('texercise_type');
            $table->boolean('is_stretch')->default(0);
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
        Schema::drop('template_exercises');
    }
}
