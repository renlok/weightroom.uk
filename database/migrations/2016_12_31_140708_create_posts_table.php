<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function ($table) {
            $table->increments('post_id');
            $table->string('url', 255)->unique();
            $table->string('title', 140);
            $table->string('description', 170);
            $table->text('content');
            $table->text('markdown');
            $table->integer('category_id');
            $table->dateTime('published_at');
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
        Schema::drop('posts');
    }
}
