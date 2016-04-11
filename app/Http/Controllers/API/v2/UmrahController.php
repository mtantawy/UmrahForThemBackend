<?php

namespace App\Http\Controllers\API\v2;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\UmrahRepository;

class UmrahController extends Controller
{
    protected $umrah;

    public function __construct(UmrahRepository $umrah)
    {
        $this->umrah = $umrah;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // sorting
        $sort_by = $request->has('sort_by') ? $request->input('sort_by') : 'created_at';
        $sort = $request->has('sort') ? $request->input('sort') : 'desc';

        // pagination
        $per_page = $request->has('per_page') ? $request->input('per_page') : 10;

        $deceased_list  = $this->umrah
                                ->getDeceasedWithNoUmrah()
                                ->orderBy($sort_by, $sort)
                                ->paginate($per_page);
        $deceased_list->transform(function ($item, $key) {
            $item->creator = $item->user;
            $item->creator->user_id = $item->creator->id;
            return $item;
        });

        // moving unset to another transform round to avoid unsetting any required objects
        $deceased_list->transform(function ($item, $key) {
            unset($item->user_id);
            unset($item->user);
            unset($item->creator->id);
            return $item;
        });

        return $deceased_list;
    }

    /**
     * Display a listing of umrahs requested by me (deceased added by me).
     *
     * @return \Illuminate\Http\Response
     */
    public function myRequests(Request $request)
    {
        // sorting
        $sort_by = $request->has('sort_by') ? $request->input('sort_by') : 'created_at';
        $sort = $request->has('sort') ? $request->input('sort') : 'desc';

        // pagination
        $per_page = $request->has('per_page') ? $request->input('per_page') : 10;

        $deceased_list  = $this->umrah
                                ->getMyRequests()
                                ->orderBy($sort_by, $sort)
                                ->paginate($per_page);
        $deceased_list->transform(function ($item, $key) {
            // these are my requests, i don't need to know creator info!
            unset($item->user_id);
            return $item;
        });

        return $deceased_list;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
                'name' => 'required|max:255',
                'sex' => 'required|in:male,female',
                'age' => 'required|integer',
                'country' => 'required|max:255',
                'city' => 'required|max:255',
                'death_cause' => 'required|string',
                'death_date' => 'required|date',
           ]);

        if ($validator->fails()) {
            return response()->json([
                    'Error' =>  'Bad Request: Validation failed.',
                    'Error Message' =>  $validator->messages()
                ], 400);
        } else {
            $deceased = $this->umrah->storeDeceased(
                $request->only('name', 'sex', 'age', 'country', 'city', 'death_cause', 'death_date')
            );

            return [
                'id'    =>  $deceased->id,
                'name'  =>  $deceased->name,
                'age'   =>  $deceased->age,
                'sex'   =>  $deceased->sex,
                'country'   =>  $deceased->country,
                'city'  =>  $deceased->city,
                'death_cause'   =>  $deceased->death_cause,
                'death_date'    =>  $deceased->death_date,
                'creator_id'    =>  $deceased->user_id
            ];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $deceased  = $this->umrah->getDeceased($id);
        return $this->prepareDeceased($deceased);
    }

    private function prepareDeceased($deceased)
    {
        // prepare creator info
        $deceased->creator = $deceased->user;
        $deceased->creator->user_id = $deceased->creator->id;
        unset($deceased->user);
        unset($deceased->user_id);
        unset($deceased->creator->id);

        // prepare umrah info
        $deceased->umrahs->transform(function ($item, $key) {
            $item->performer = $item->user;
            return $item;
        });
        // unset()
        $deceased->umrahs->transform(function ($item, $key) {
            unset($item->deceased_id);
            unset($item->umrah_status_id);
            unset($item->user_id);
            unset($item->user);
            return $item;
        });

        return $deceased;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function updateStatus(Request $request, $deceased, $status)
    {
        $result = $this->umrah->updateStatus($deceased, $status);
        if ($result instanceof \App\Deceased) {
            return $this->prepareDeceased($result);
        } else {
            // an error occurred, ust return error msg
            return response()->json([
                'error_message'    =>  $result
            ]);
        }
    }
}
