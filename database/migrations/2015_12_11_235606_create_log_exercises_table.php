<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_exercises', function (Blueprint $table) {
            $table->increments('logex_id');
            $table->date('logex_date');
            $table->integer('log_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('exercise_id')->unsigned()->index();
            $table->double('logex_volume', 20, 3);
            $table->integer('logex_reps');
            $table->integer('logex_sets');
            $table->integer('logex_comment');
            $table->double('logex_1rm', 20, 3);
            $table->smallInteger('logex_order');
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
        Schema::drop('log_exercises');
    }
}
