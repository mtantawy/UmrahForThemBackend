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
}
