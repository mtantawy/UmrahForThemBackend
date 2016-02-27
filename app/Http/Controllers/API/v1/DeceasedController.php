<?php

namespace app\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use App\Deceased;

class DeceasedController extends Controller
{
    private $oauth_user;

    public function __construct(Request $request)
    {
        $this->oauth_user = $this->getUserByAccessToken($request);
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

        return Deceased::orderBy($sort_by, $sort)
                ->select('deceased.*')
                ->leftjoin('umrahs', 'umrahs.deceased_id', '=', 'deceased.id')
                ->whereNull('umrahs.created_at')
                ->with('user')
                ->paginate($per_page);
    }

    public function myRequests(Request $request)
    {
        // sorting
        $sort_by = $request->has('sort_by') ? $request->input('sort_by') : 'created_at';
        $sort = $request->has('sort') ? $request->input('sort') : 'desc';

        // pagination
        $per_page = $request->has('per_page') ? $request->input('per_page') : 10;

        return Deceased::orderBy($sort_by, $sort)
                ->where('user_id', $this->oauth_user->owner_id)
                ->paginate($per_page);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return Deceased::Create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $deceased = Deceased::find($id);
        if (null === $deceased) {
            return response()->json('Deceased not found.', 404);
        } else {
            return $deceased;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return Response::json(Deceased::findOrFail($id)
                ->update($request->only(['name', 'sex', 'age', 'country', 'city', 'death_cause', 'death_date'])));
    }

    public function updateStatus(Request $request, $deceased, $status)
    {
        switch ($status) {
            case 1:
                # 1 => 'In Progress', so we create a Umrah
                if (0 == Deceased::findOrFail($deceased)->umrahs()->where('user_id', $this->oauth_user->owner_id)->count()) {
                    Deceased::findOrFail($deceased)->umrahs()->create([
                            'user_id' => $this->oauth_user->owner_id,
                            'deceased' => $deceased,
                            'umrah_status_id' => 1,
                        ]);
                } else {
                    $umrah = Deceased::findOrFail($deceased)->umrahs()->where('user_id', $this->oauth_user->owner_id)->first();
                    $umrah->umrah_status_id = 1;
                    $umrah->save();
                }
                break;

            case 2:
                # 2 => 'Done', just update umrah status of the current logged in user, if exists
                $umrah = Deceased::findOrFail($deceased)->umrahs()->where('user_id', $this->oauth_user->owner_id)->first();
                $umrah->umrah_status_id = 2;
                $umrah->save();
                break;

            case 3:
                # 3 => 'cancelled', delete umrah
                return Response::json(Deceased::findOrFail($deceased)->umrahs()->where('user_id', $this->oauth_user->owner_id)->first()->delete());
                break;

            default:
                return Response::json(false);
                break;
        }

        return Deceased::findOrFail($deceased)->umrahs()->where('user_id', $this->oauth_user->owner_id)->first();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deceased = Deceased::findOrFail($id);

        return Response::json($deceased->delete());
    }
}
