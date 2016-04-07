<?php

namespace App\Http\Controllers\API\v2;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Response;

use App\User;

class UserController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return User::Create(
            array_merge(
                $request->only(['name', 'email']),
                [
                    'password' => bcrypt($request->input('password')),
                    'remember_token' => str_random(10),
                ]
            )
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        if (is_null($id)) {
            $id = \Authorizer::getResourceOwnerId();
        }
        return User::findOrFail($id);
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
        if ($id != \Auth::User()->id) {
            return Response::json('You are not authorized to edit this resource.', 401);
        }
        $profile = $request->only(['name', 'email']);
        if ($request->has('password') && !empty($request->input('password'))) {
            $profile = array_merge($profile, ['password' => bcrypt($request->input('password'))]);
        }
        return Response::json(User::findOrFail($id)
                ->update($profile));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        return Response::json($user->delete());
    }
}
