<?php

namespace App\Http\Controllers\API\v2;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\UmrahRepository;
use LucaDegasperi\OAuth2Server\Exceptions\NoActiveAccessTokenException;

class UmrahController extends Controller
{
    protected $umrah;

    public function __construct(UmrahRepository $umrah)
    {
        $this->umrah = $umrah;
        try {
            $this->umrah->auth_user_id = \Authorizer::getResourceOwnerId();
        } catch (NoActiveAccessTokenException $e) {
            // nothing to be done here, some requests can be done without access_token
        }
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
        $sort = $request->has('sort') ? $request->input('sort') : 'asc';

        $deceased_list  = $this->umrah
                                ->getDeceasedWithNoUmrah()
                                ->orderBy($sort_by, $sort);

        $deceased_list = $this->paginateIfNeeded($request, $deceased_list);
                                
        $deceased_list->transform(function ($item, $key) {
            return $this->prepareDeceased($item);
        });

        $deceased_list = $this->prepareResponse($request, $deceased_list);

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

        $deceased_list  = $this->umrah
                                ->getMyRequests()
                                ->orderBy($sort_by, $sort);

        $deceased_list = $this->paginateIfNeeded($request, $deceased_list);

        $deceased_list->transform(function ($item, $key) {
            return $this->prepareDeceased($item);
        });

        $deceased_list = $this->prepareResponse($request, $deceased_list);

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
            $error_message = collect($validator->messages())->flatten()->reduce(function ($messages, $message) {
                if (empty($messages)) {
                    return $message;
                } else {
                    return $messages . '، ' . $message;
                }
            }, '');
            return response()->json([
                    'error_message' =>  $error_message
                ], 400);
        } else {
            $deceased = $this->umrah->storeDeceased(
                $request->only([
                    'name', 'sex', 'age', 'country', 'city', 'death_cause', 'death_date', 'done_umrah_before'
                ])
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
            if (!$item->user->hide_performer_info || $item->user->id == \Authorizer::getResourceOwnerId()) {
                $item->performer = $item->user;
            }
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
        \Auth::loginUsingId(\Authorizer::getResourceOwnerId());
        $this->authorize('update', $this->umrah->getDeceased($id));

        $data = $request->only([
            'name', 'sex', 'age', 'country', 'city', 'death_cause', 'death_date', 'done_umrah_before'
        ]);
        $result = $this->umrah->updateDeceased($id, $data);
        if ($result instanceof \App\Deceased) {
            return $this->prepareDeceased($result);
        } else {
            // an error occurred, ust return error msg
            return response()->json([
                'error_message'    =>  $result
            ]);
        }
    }

    public function updateStatus(Request $request, $deceased, $status)
    {
        $result = $this->umrah->updateStatus($deceased, $status);
        if ($result instanceof \App\Deceased) {
            return $this->prepareDeceased($result);
        } elseif (true === $result) {
            return response()->json([
                'message'   =>  'Umrah Cancelled Successfully'
            ]);
        } else {
            // an error occurred, ust return error msg
            return response()->json([
                'error_message'    =>  $result
            ]);
        }
    }

    public function performedByMe(Request $request)
    {
        // sorting
        $sort_by = $request->has('sort_by') ? $request->input('sort_by') : 'created_at';
        $sort = $request->has('sort') ? $request->input('sort') : 'desc';

        $deceased_list  = $this->umrah
                                ->getUmrahsPerformedByMe()
                                ->orderBy($sort_by, $sort);

        $deceased_list = $this->paginateIfNeeded($request, $deceased_list);

        $deceased_list->transform(function ($item, $key) {
            return $this->prepareDeceased($item);
        });

        $deceased_list = $this->prepareResponse($request, $deceased_list);

        return $deceased_list;
    }

    private function paginateIfNeeded(Request $request, $collection)
    {
        if ($request->has('no_pagination') && $request->input('no_pagination')) {
            // do NOT paginate
            return $collection->get();
        } else {
            // pagination
            $per_page = $request->has('per_page') ? $request->input('per_page') : 10;

            return $collection->paginate($per_page);
        }
    }

    private function prepareResponse(Request $request, $collection)
    {
        return $request->input('no_pagination', false) ? ['data' => $collection] : $collection;
    }

    public function destroy($id)
    {
        if ($this->umrah->deleteDeceased($id)) {
            return response()->json([
                'message'   =>  'Umrah Request Deleted Successfully'
            ]);
        } else {
            return response()->json([
                'error_message' =>  'Unauthorized',
            ], 401);
        }
    }

    public function search(Request $request)
    {
        return response()->json($this->umrah->searchDeceased($request));
    }
}
