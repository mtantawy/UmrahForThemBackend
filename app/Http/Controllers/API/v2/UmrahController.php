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
    public function index()
    {
        //
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
            return \App\Deceased::Create(
                array_merge(
                    $request->all(),
                    ['user_id' => \Authorizer::getResourceOwnerId()]
                )
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
