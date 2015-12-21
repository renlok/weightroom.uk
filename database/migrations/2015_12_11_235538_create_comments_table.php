<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('comment_id');
            $table->string('comment_type'); // NEW
            $table->integer('parent_id')->unsigned()->index();
            $table->text('comment');
            $table->integer('log_id')->unsigned()->index();
            $table->date('log_date');
            $table->integer('sender_user_id')->unsigned();
            $table->integer('receiver_user_id')->unsigned();
            $table->dateTime('comment_date');
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
        Schema::drop('comments');
    }
}
