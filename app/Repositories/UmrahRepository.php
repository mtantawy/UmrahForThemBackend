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

    public function getDeceased($id)
    {
        $deceased = Deceased::findOrFail($id);
        return $deceased->load('user', 'umrahs', 'umrahs.umrahStatus', 'umrahs.user');
    }

    public function updateStatus($deceased_id, $status_id)
    {
        switch ($status_id) {
            case 1:
                # 1 => 'In Progress', so we create a Umrah
                // first check if there is a umrah with status (done, or in progress) for this deceased then return msg
                $count_of_umrahs_for_deceased = Deceased::findOrFail($deceased_id)
                                                        ->umrahs()
                                                        ->where('user_id', '!=', \Authorizer::getResourceOwnerId())
                                                        ->leftjoin('umrah_statuses', 'umrah_statuses.id', '=', 'umrahs.umrah_status_id')
                                                        ->whereIn('umrah_statuses.id', [1, 2])
                                                        ->count();
                if (0 != $count_of_umrahs_for_deceased) {
                    return 'There is already an Umrah being performed for this Deceased.';
                }
                // if no umrah for this user for this deceased, create one with status in progress
                if (0 == Deceased::findOrFail($deceased_id)->umrahs()->where('user_id', \Authorizer::getResourceOwnerId())->count()) {
                    Deceased::findOrFail($deceased_id)->umrahs()->create([
                            'user_id' => \Authorizer::getResourceOwnerId(),
                            'deceased' => $deceased_id,
                            'umrah_status_id' => 1,
                        ]);
                } else {
                    // else update the current umrah
                    $umrah = Deceased::findOrFail($deceased_id)->umrahs()->where('user_id', \Authorizer::getResourceOwnerId())->first();
                    $umrah->umrah_status_id = 1;
                    $umrah->save();
                }
                break;

            case 2:
                # 2 => 'Done', just update umrah status of the current logged in user, if exists
                $umrah = Deceased::findOrFail($deceased_id)->umrahs()->where('user_id', \Authorizer::getResourceOwnerId())->first();
                $umrah->umrah_status_id = 2;
                $umrah->save();
                break;

            case 3:
                # 3 => 'cancelled', delete umrah
                return Deceased::findOrFail($deceased_id)->umrahs()->where('user_id', \Authorizer::getResourceOwnerId())->first()->delete();
                break;

            default:
                return 'An Error Occurred, please try again.';
                break;
        }

        return $this->getDeceased($deceased_id);
    }

    public function updateDeceased($deceased_id, $data)
    {
        $deceased = Deceased::findOrFail($deceased_id);
        if ($deceased->update($data)) {
            return $deceased;
        } else {
            return 'An Error Occurred, please try again.';
        }
    }
}
