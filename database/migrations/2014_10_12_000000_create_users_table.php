<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('user_name');
            $table->string('user_email')->unique();
            $table->string('user_password', 60);
            $table->rememberToken();
            $table->timestamps();
            $table->double('user_weight', 7, 2);
            $table->enum('user_unit', ['kg', 'lb'])->default('kg');
            $table->enum('user_gender', ['m', 'f'])->default('m');
            $table->string('user_showreps', 15)->default('[1,2,3,5,8,10]');
            $table->string('user_showextrareps')->default('[12,15]');
            $table->integer('user_squatid')->default('0');
            $table->integer('user_deadliftid')->default('0');
            $table->integer('user_benchid')->default('0');
            $table->integer('user_snatchid')->default('0');
            $table->integer('user_cleanjerkid')->default('0');
            $table->boolean('user_volumeincfails')->default('0');
            $table->boolean('user_volumeincwarmup')->default('1');
            $table->boolean('user_weekstart')->default('0');
            $table->smallInteger('user_limitintensity')->default('0');
            $table->boolean('user_limitintensitywarmup')->default('0');
            $table->enum('user_showintensity', ['h', 'p', 'a'])->default('p'); // h = hide, p = percent, a = absolute
            $table->boolean('user_showinol')->default('0');
            $table->boolean('user_inolincwarmup')->default('0');
            $table->boolean('user_firstlog')->default('1');
            $table->boolean('user_private')->default('0');
            $table->boolean('user_beta')->default('0');
            $table->boolean('user_admin')->default('0');
            $table->integer('user_don_level')->default('0');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
