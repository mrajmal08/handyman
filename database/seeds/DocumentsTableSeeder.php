<?php

use Illuminate\Database\Seeder;

class DocumentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('documents')->truncate();
        DB::table('documents')->insert([
            [
                'name' => 'Address Proof',
                'type' => 'DOCUMENT',
            ],
            [
                'name' => 'Photo Proof',
                'type' => 'DOCUMENT',
            ],
        ]);
    }
}
