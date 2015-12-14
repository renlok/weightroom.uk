<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->index();
            $table->text('comment');
            $table->integer('log_id')->index();
            $table->date('log_date');
            $table->integer('sender_user_id');
            $table->integer('receiver_user_id');
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
        Schema::drop('log_comments');
    }
}
