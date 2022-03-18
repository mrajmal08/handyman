<?php

use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->truncate();
        DB::table('admins')->insert([
            'name' => 'Xuber Services',
            'email' => 'admin@xuber.com',
            'password' => bcrypt('123456'),
        ],[
            'name' => 'Xuber Services',
            'email' => 'admin@demo.com',
            'password' => bcrypt('123456'),
        ]);
    }
}
