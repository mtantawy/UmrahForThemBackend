<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
        'city'  =>  $faker->city,
        'country'   =>  $faker->country,
        'sex'   =>  $faker->boolean(50) ? 'male' : 'female',
        'hide_performer_info'   =>  $faker->boolean(50) ? true : false,
    ];
});

$factory->define(App\Deceased::class, function (Faker\Generator $faker) {
    return [
        'name'      =>  $faker->name,
        'sex'       =>  $faker->boolean(50) ? 'male' : 'female',
        'age'       =>  $faker->numberBetween(1, 100),
        'country'   =>  $faker->country,
        'city'      =>  $faker->city,
        'death_cause'   =>  $faker->text,
        'death_date'    =>  $faker->date('Y-m-d', 'now'),
        'user_id'   =>  factory(App\User::class)->create()->id,
        'done_umrah_before' =>  $faker->boolean(50) ? true : false,
        'death_cause_id'    => \App\DeathCause::all()->random()->id,
    ];
});

$factory->define(App\Umrah::class, function (Faker\Generator $faker) {
    return [
        'user_id'   =>  factory(App\User::class)->create()->id,
        'umrah_status_id'   => \App\UmrahStatus::whereIn('status', ['In Progress', 'Done'])->get()->random()->id,
        'deceased_id'   =>  factory(App\Deceased::class)->create()->id,
    ];
});

$factory->defineAs(App\Umrah::class, 'umrah_no_deceased_id', function (Faker\Generator $faker) {
    return [
        'user_id'   =>  factory(App\User::class)->create()->id,
        'umrah_status_id'   => \App\UmrahStatus::whereIn('status', ['In Progress', 'Done'])->get()->random()->id,
    ];
});

$factory->defineAs(App\Umrah::class, 'umrah_no_user_id', function (Faker\Generator $faker) {
    return [
        'umrah_status_id'   => \App\UmrahStatus::whereIn('status', ['In Progress', 'Done'])->get()->random()->id,
        'deceased_id'   =>  factory(App\Deceased::class)->create()->id,
    ];
});
