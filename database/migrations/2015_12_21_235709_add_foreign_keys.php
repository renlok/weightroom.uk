<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exercises', function ($table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
        Schema::table('exercise_records', function ($table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('exercise_id')->references('exercise_id')->on('exercises');
        });
        Schema::table('invite_codes', function ($table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
        Schema::table('logs', function ($table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
        Schema::table('log_comments', function ($table) {
            $table->foreign('log_id')->references('log_id')->on('logs')->onDelete('cascade');
            $table->foreign('sender_user_id')->references('user_id')->on('users');
            $table->foreign('receiver_user_id')->references('user_id')->on('users');
        });
        Schema::table('log_exercises', function ($table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('exercise_id')->references('exercise_id')->on('exercises');
            $table->foreign('log_id')->references('log_id')->on('logs')->onDelete('cascade');
        });
        Schema::table('log_items', function ($table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('log_id')->references('log_id')->on('logs')->onDelete('cascade');
            $table->foreign('logex_id')->references('logex_id')->on('log_exercises');
            $table->foreign('exercise_id')->references('exercise_id')->on('exercises');
        });
        Schema::table('notifications', function ($table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
        Schema::table('user_follows', function ($table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('follow_user_id')->references('user_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exercises', function ($table) {
            $table->dropForeign('exercises_user_id_foreign');
        });
        Schema::table('exercise_records', function ($table) {
            $table->dropForeign('exercise_records_user_id_foreign');
            $table->dropForeign('exercise_records_exercise_id_foreign');
        });
        Schema::table('invite_codes', function ($table) {
            $table->dropForeign('invite_codes_user_id_foreign');
        });
        Schema::table('logs', function ($table) {
            $table->dropForeign('logs_user_id_foreign');
        });
        Schema::table('log_comments', function ($table) {
            $table->dropForeign('log_comments_user_id_foreign');
            $table->dropForeign('log_comments_sender_user_id_foreign');
            $table->dropForeign('log_comments_receiver_user_id_foreign');
        });
        Schema::table('log_exercises', function ($table) {
            $table->dropForeign('log_exercises_user_id_foreign');
            $table->dropForeign('log_exercises_exercise_id_foreign');
            $table->dropForeign('log_exercises_log_id_foreign');
        });
        Schema::table('log_items', function ($table) {
            $table->dropForeign('log_items_user_id_foreign');
            $table->dropForeign('log_items_exercise_id_foreign');
            $table->dropForeign('log_items_log_id_foreign');
            $table->dropForeign('log_items_logex_id_foreign');
        });
        Schema::table('notifications', function ($table) {
            $table->dropForeign('notifications_user_id_foreign');
        });
        Schema::table('user_follows', function ($table) {
            $table->dropForeign('user_follows_user_id_foreign');
            $table->dropForeign('user_follows_follow_user_id_foreign');
        });
    }
}
