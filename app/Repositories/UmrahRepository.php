<?php

namespace App\Repositories;

use App\User;
use App\Umrah;
use App\Deceased;
use Carbon\Carbon;

class UmrahRepository
{
    public $auth_user_id;

    public function storeDeceased($data)
    {
        $data = array_merge(
            $data,
            ['user_id' => $this->auth_user_id]
        );

        return Deceased::Create($data);
    }

    public function getDeceasedWithNoUmrah()
    {
        return Deceased::select('deceased.*')
                ->leftjoin('umrahs', 'umrahs.deceased_id', '=', 'deceased.id')
                ->whereNull('umrahs.created_at')
                ->with('user', 'umrahs', 'umrahs.umrahStatus', 'umrahs.user');
    }

    public function getMyRequests()
    {
        return Deceased::where('user_id', $this->auth_user_id)
                ->with('user', 'umrahs', 'umrahs.umrahStatus', 'umrahs.user');
    }

    public function getDeceased($id)
    {
        $deceased = Deceased::findOrFail($id);
        return $deceased->load('user', 'umrahs', 'umrahs.umrahStatus', 'umrahs.user');
    }

    public function updateStatus($deceased_id, $status_id)
    {
        $umrah = null;
        switch ($status_id) {
            case 1:
                # 1 => 'In Progress', so we create a Umrah
                // first check if there is a umrah with status (done, or in progress) for this deceased then return msg
                $count_of_umrahs_for_deceased = Deceased::findOrFail($deceased_id)
                                                        ->umrahs()
                                                        ->where('user_id', '!=', $this->auth_user_id)
                                                        ->leftjoin('umrah_statuses', 'umrah_statuses.id', '=', 'umrahs.umrah_status_id')
                                                        ->whereIn('umrah_statuses.id', [1, 2])
                                                        ->count();
                if (0 != $count_of_umrahs_for_deceased) {
                    return 'There is already an Umrah being performed for this Deceased.';
                }
                // if no umrah for this user for this deceased, create one with status in progress
                if (0 == Deceased::findOrFail($deceased_id)->umrahs()->where('user_id', $this->auth_user_id)->count()) {
                    Deceased::findOrFail($deceased_id)->umrahs()->create([
                            'user_id' => $this->auth_user_id,
                            'deceased' => $deceased_id,
                            'umrah_status_id' => 1,
                        ]);
                } else {
                    // else update the current umrah
                    $umrah = Deceased::findOrFail($deceased_id)->umrahs()->where('user_id', $this->auth_user_id)->first();
                    $umrah->umrah_status_id = 1;
                    $umrah->save();
                }
                break;

            case 2:
                # 2 => 'Done', just update umrah status of the current logged in user, if exists
                $umrah = Deceased::findOrFail($deceased_id)->umrahs()->where('user_id', $this->auth_user_id)->first();
                $umrah->umrah_status_id = 2;
                $umrah->save();
                break;

            case 3:
                # 3 => 'cancelled', delete umrah
                return Deceased::findOrFail($deceased_id)->umrahs()->where('user_id', $this->auth_user_id)->first()->delete();
                break;

            default:
                return 'An Error Occurred, please try again.';
                break;
        }

        if (!is_null($umrah)) {
            $this->sendUmrahStatusUpdateEmail($status_id, $umrah);
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

    public function getUmrahsPerformedByMe()
    {
        return Deceased::select('deceased.*')
                ->leftjoin('umrahs', 'umrahs.deceased_id', '=', 'deceased.id')
                ->where('umrahs.user_id', $this->auth_user_id)
                ->with('user', 'umrahs', 'umrahs.umrahStatus', 'umrahs.user');
    }

    private function sendUmrahStatusUpdateEmail($status_id, $umrah)
    {
        switch ($status_id) {
            case 1:
                $umrah_status = trans('emails.in_progress');
                break;

            case 2:
                $umrah_status = trans('emails.done');
                break;

            default:
                return;
                break;
        }
        \Mail::send('emails.umrah_status_update', [
            'creator_name' => $umrah->deceased->user->name,
            'deceased_name' => $umrah->deceased->name,
            'umrah_status' => $umrah_status
        ], function ($message) use ($umrah) {
            $message->to($umrah->deceased->user->email, $umrah->deceased->user->name);
            $message->from('umrah_updates@umrah4them.com', 'Umrah4Them.com');
            $message->subject(trans('emails.status_update_subject'));
            $message->replyTo('noreply@umrah4them.com', $name = null);
        });
    }

    public function getStalledUmrahs($days = 3)
    {
        return Umrah::where('umrah_status_id', 1) // in progress
                        ->where('updated_at', '<', Carbon::now()->subDays($days))
                        ->get();
    }

    public function sendStalledUmrahEmails($umrah)
    {
        // send email to creator
        \Mail::send('emails.umrah_stalled_cancellation_creator', [
            'creator_name' => $umrah->deceased->user->name,
            'deceased_name' => $umrah->deceased->name,
        ], function ($message) use ($umrah) {
            $message->to($umrah->deceased->user->email, $umrah->deceased->user->name);
            $message->from('umrah_updates@umrah4them.com', 'Umrah4Them.com');
            $message->subject(trans('emails.status_update_subject'));
            $message->replyTo('noreply@umrah4them.com', $name = null);
        });
        // send email to performer
        \Mail::send('emails.umrah_stalled_cancellation_performer', [
            'performer_name' => $umrah->user->name,
            'deceased_name' => $umrah->deceased->name,
        ], function ($message) use ($umrah) {
            $message->to($umrah->user->email, $umrah->user->name);
            $message->from('umrah_updates@umrah4them.com', 'Umrah4Them.com');
            $message->subject(trans('emails.status_update_subject'));
            $message->replyTo('noreply@umrah4them.com', $name = null);
        });
        // cancel umrah!
        $this->updateStatus($umrah->deceased_id, 3);
    }

    public function deleteDeceased($id)
    {
        $deceased = Deceased::findOrFail($id);
        // check if auth user is the one who added this deceased
        if ($this->auth_user_id != $deceased->user_id) {
            return false;
        }
        // check if the deceased doesn't have any umrahs done or in progress
        if (0 != $deceased->umrahs()->count()) {
            return false;
        }

        return $deceased->delete();
    }

    public function searchDeceased($filters)
    {
        $string_columns = [
            'name',
            'country',
            'city',
            'death_cause'
        ];

        $all_columns = [
            'name',
            'sex',
            'age',
            'country',
            'city',
            'death_cause',
            'death_date'
        ];

        if ($filters->has('keyword') && (strlen($filters->input('keyword')) >= 3 || is_numeric($filters->input('keyword')))) {
            $keyword = $filters->input('keyword');
            $keyword_like = '%' . str_replace(' ', '%', $keyword) . '%';
            // search for the keyword in everything
            return Deceased::where('name', 'LIKE', $keyword_like)
                             ->orWhere('sex', $keyword)
                             ->orWhere('age', $keyword)
                             ->orWhere('country', 'LIKE', $keyword_like)
                             ->orWhere('city', 'LIKE', $keyword_like)
                             ->orWhere('death_cause', 'LIKE', $keyword_like)
                             ->orWhere('death_date', $keyword)
                             ->paginate();
        } else {
            $query = Deceased::Query();
            foreach ($filters->all() as $key => $value) {
                if (!in_array($key, $all_columns)) {
                    continue;
                }
                // !is_numeric to avoid hitting the age column
                if (strlen($value) < 3 && !is_numeric($value)) {
                    continue;
                }

                if (in_array($key, $string_columns)) {
                    $value_like = '%' . str_replace(' ', '%', $value) . '%';
                    $query->orWhere($key, 'LIKE', $value_like);
                } else {
                    $query->orWhere($key, '=', $value);
                }
            }

            if (empty($query->getBindings())) {
                // we will reach here if NO where statements were added
                // we don't want to return all results, instead want to return no results
                // so we fake a bad query
                // this needs a better idea!
                $query->WhereRaw('1=0');
            }
            return $query->paginate();
        }
    }
}
