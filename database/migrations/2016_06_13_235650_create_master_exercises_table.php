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
            $table->string('tain_action');
            $table->string('tinor_action');
            $table->string('texercise_type');
            $table->boolean('is_stretch');
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
        Schema::drop('master_exercises');
    }
}
