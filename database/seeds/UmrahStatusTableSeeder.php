<?php

use Illuminate\Database\Seeder;
use App\UmrahStatus;

class UmrahStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        UmrahStatus::firstOrCreate([
            'status' => 'In Progress'
        ]);
        UmrahStatus::firstOrCreate([
            'status' => 'Done'
        ]);
        UmrahStatus::firstOrCreate([
            'status' => 'Cancelled'
        ]);
    }
}
