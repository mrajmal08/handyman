<?php

use Illuminate\Database\Seeder;

use Carbon\Carbon;

class ServiceTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('service_types')->truncate();
        DB::table('service_types')->insert([
            [
                'name' => 'Electrician',
                'provider_name' => 'Electrician',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/electrician.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Plumbing',
                'provider_name' => 'Plumber',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/plumbing.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Carpenter',
                'provider_name' => 'Carpenter',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/carpenter.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Mechanic',
                'provider_name' => 'Mechanic',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/mechanic.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Car Wash',
                'provider_name' => 'Car Wash',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/carwash.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Cleaning',
                'provider_name' => 'Cleaning',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/cleaning.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Decorations',
                'provider_name' => 'Decorations',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/decorations.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Event Handler',
                'provider_name' => 'Event Handler',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/event_handler.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Handyman',
                'provider_name' => 'Handyman',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/handyman.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Nanny',
                'provider_name' => 'Nanny',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/nanny.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Painter',
                'provider_name' => 'Painter',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/painting.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'PhotoGraphy',
                'provider_name' => 'PhotoGraphy',
                'fixed' => 20,
                'price' => 10,
                'status' => 1,
                'image' => url('seeddata/service/photoGraphy.png'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
