<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->increments('exercise_id');
	        $table->string('exercise_name');
            $table->string('exercise_name_clean');
            $table->integer('user_id')->unsigned()->index();
            $table->boolean('is_time')->default('0');
            $table->boolean('is_endurance')->default('0'); // NEW colomn
            $table->boolean('is_distance')->default('0'); // NEW colomn
            $table->boolean('exercise_update_prs')->default('0'); // NEW colomn
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
        Schema::drop('exercises');
    }
}
