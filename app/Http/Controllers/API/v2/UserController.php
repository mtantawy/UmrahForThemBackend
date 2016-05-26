<?php

namespace App\Http\Controllers\API\v2;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Auth\AuthController;
use Response;

use App\User;

class UserController extends AuthController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                    'error_message' =>  $validator->messages()
                ], 400);
        } else {
            return User::Create(
                array_merge(
                    $request->only(['name', 'email', 'sex', 'country', 'city']),
                    [
                        'password' => bcrypt($request->input('password')),
                        'remember_token' => str_random(10),
                    ]
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
    public function show($id = null)
    {
        if (is_null($id)) {
            $id = \Authorizer::getResourceOwnerId();
        }
        $user = User::findOrFail($id);
        $user->umrah_requests_count = $user->getUmrahRequestsCount();
        $user->in_progress_umrahs_count = $user->getUmrahsCountByStatusID(1);
        $user->done_umrahs_count = $user->getUmrahsCountByStatusID(2);

        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = \Authorizer::getResourceOwnerId();

        $profile = [];
        if ($request->has('password') && !empty($request->input('password'))) {
            $profile = array_merge($profile, ['password' => bcrypt($request->input('password'))]);
        }
        if ($request->has('email') && !empty($request->input('email'))) {
            $profile = array_merge(
                $profile,
                $request->only(['name', 'email', 'sex', 'country', 'city'])
            );
        }
        if (User::findOrFail($id)->update($profile)) {
            return Response::json(User::find($id));
        } else {
            abort(500);
        }
    }

    public function resetPassword(Request $request)
    {
        $email = $request->input('email');
        $users = User::where('email', $email)->get();
        if ($users->count()) {
            $status = $users->first()->resetPassword();
            if (false === $status) {
                return response()->json([
                    'error_message' =>  'An error occurred while trying to sent password reset email.',
                ], 500);
            }
        }

        // in all cases we return response that we sent an email to the address except if we failed to send password reset email
        return response()->json([
                'message' =>  'We sent a password reset email to: '.$email,
            ]);
    }
}
