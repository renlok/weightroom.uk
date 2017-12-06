<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExerciseGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercise_groups', function ($table) {
            $table->increments('exgroup_id');
            $table->integer('user_id');
            $table->string('exgroup_name');
            $table->string('exgroup_name_clean');
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
        Schema::drop('exercise_groups');
    }
}
