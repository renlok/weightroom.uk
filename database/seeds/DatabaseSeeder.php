<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::table('users')->insert([
            'user_name' => 'test',
            'user_email' => str_random(10).'@gmail.com',
            'user_password' => bcrypt('test123'),
        ]);

        // $this->call(UserTableSeeder::class);

        Model::reguard();
    }
}
