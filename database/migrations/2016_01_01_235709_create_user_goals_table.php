<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_goals', function (Blueprint $table) {
            $table->increments('goal_id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('exercise_id')->unsigned()->index();
            $table->integer('goal_value')->unsigned()->index();
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
        Schema::drop('user_follows');
    }
}
