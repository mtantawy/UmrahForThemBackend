<?php

namespace App\Http\Controllers\API\v2;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Response;
use Validator;
use Authorizer;

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
        $validator = $this->validator($request->all());

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
            return User::Create(
                array_merge(
                    $request->only(['name', 'email', 'sex', 'country', 'city']),
                    ['hide_performer_info' => $request->input('hide_performer_info', false)],
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
        if ($request->has('email') && !empty($request->input('email'))) {
            $profile = array_merge(
                $request->only(['name', 'email', 'sex', 'country', 'city', 'hide_performer_info']),
                ['hide_performer_info' => $request->input('hide_performer_info', false)]
            );
        }

        $validator = $this->validator($request->all(), true);

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
            if (User::findOrFail($id)->update($profile)) {
                return Response::json(User::find($id));
            } else {
                abort(500);
            }
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

    protected function validator(array $data, $is_update = false)
    {
        $rules = [
            'name' => 'required|max:255',
        ];

        if (!$is_update) {
            $rules['password'] = 'required';
            $rules['email'] = "required|email|max:255|unique:users";
        } else {
            $user_id = \Authorizer::getResourceOwnerId();
            $rules['email'] = "required|email|max:255|unique:users,email,{$user_id}";
        }
        return Validator::make($data, $rules);
    }

    public function login(Request $request)
    {
        try {
            $access_token_info = Authorizer::issueAccessToken();
        } catch (InvalidCredentialsException $e) {
            return response()->json([
                    'error_message' =>  'Wrong Credentials.'
                ], 400);
        }
        $user = User::where('email', $request->input('username'))->first();
        return Response::json([
            'access_token_info' => $access_token_info,
            'user_info' =>  $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        $id = \Authorizer::getResourceOwnerId();

        $profile = [];
        if (!empty($request->input('old_password', '')) && !empty($request->input('password', ''))) {
            // validate old_password first
            $credentials = [
                'email' => User::findOrFail($id)->email,
                'password'  =>  trim($request->input('old_password'))
            ];
            if (\Auth::attempt($credentials)) {
                $profile = [
                    'password' => bcrypt($request->input('password'))
                ];
            } else {
                return response()->json([
                        'error_message' =>  trans('auth.wrong_password')
                    ], 400);
            }
        }

        if (User::findOrFail($id)->update($profile)) {
            return Response::json(User::find($id));
        } else {
            abort(500);
        }
    }
}
