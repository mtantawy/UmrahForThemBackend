<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\UmrahStatus;
use App\Deceased;
use App\Umrah;
use App\User;

class UmrahTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        Umrah::Create([
                'user_id'           => User::all()->random()->id,
                'deceased_id'       => Deceased::all()->random()->id,
                'umrah_status_id'   => UmrahStatus::all()->random()->id,
            ]);
    }
}
