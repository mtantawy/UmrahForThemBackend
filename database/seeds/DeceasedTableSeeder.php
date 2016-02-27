<?php

use Illuminate\Database\Seeder;

use Faker\Factory as Faker;
use App\Deceased;
use App\User;

class DeceasedTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        Deceased::Create([
        		'name'        =>	$faker->name,
        		'sex'         =>	$faker->boolean(50) ? 'male' : 'female',
        		'age'         =>	$faker->numberBetween(1, 60),
        		'country'     =>	$faker->country,
        		'city'        =>	$faker->city,
        		'death_cause' =>	$faker->text,
        		'death_date'  =>	$faker->date('Y-m-d', 'now'),
                'user_id'     =>    User::all()->random()->id,
        	]);
    }
}
