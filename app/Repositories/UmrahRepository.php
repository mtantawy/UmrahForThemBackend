<?php

namespace App\Repositories;

use App\User;
use App\Umrah;
use App\Deceased;

class UmrahRepository
{
    public function storeDeceased($data)
    {
        $data = array_merge(
            $data,
            ['user_id' => \Authorizer::getResourceOwnerId()]
        );

        return Deceased::Create($data);
    }

    public function getDeceasedWithNoUmrah()
    {
        return Deceased::select('deceased.*')
                ->leftjoin('umrahs', 'umrahs.deceased_id', '=', 'deceased.id')
                ->whereNull('umrahs.created_at')
                ->with('user');
    }

    public function getMyRequests()
    {
        return Deceased::where('user_id', \Authorizer::getResourceOwnerId())
                ->with('umrahs');
    }
}
