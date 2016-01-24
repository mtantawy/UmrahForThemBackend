<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Umrah;

class UmrahController extends Controller
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
        // pagination
        $per_page    =   $request->has('per_page') ? $request->input('per_page') : 10 ;
        
        return Umrah::orderBy('created_at', 'desc')
                ->where('user_id', $this->oauth_user->owner_id)
                ->with('umrahStatus', 'deceased')
                ->whereIn('umrah_status_id', [1, 2])
                ->paginate($per_page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $umrah = Umrah::findOrFail($id);
        $this->authorize('show', $umrah);

        return $umrah->load('umrahStatus', 'deceased', 'user');
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
}
