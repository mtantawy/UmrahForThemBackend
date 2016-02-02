<?php

use Illuminate\Database\Seeder;
use App\UmrahStatus;

class UmrahStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        UmrahStatus::firstOrCreate([
            'id'     => 1,
            'status' => 'In Progress'
        ]);
        UmrahStatus::firstOrCreate([
            'id'     => 2,
            'status' => 'Done'
        ]);
        UmrahStatus::firstOrCreate([
            'id'     => 3,
            'status' => 'Cancelled'
        ]);
    }
}
