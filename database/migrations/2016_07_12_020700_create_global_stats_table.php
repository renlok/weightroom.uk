<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_stats', function (Blueprint $table) {
			$table->increments('gstat_id');
            $table->date('gstat_date');
            $table->integer('total_users');
            $table->integer('active_users_1m');
            $table->integer('active_users_3m');
            $table->integer('ever_active_users');
            $table->integer('total_comments');
            $table->integer('total_comment_replys');
            $table->integer('total_logs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('global_stats');
    }
}
