<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getUserByAccessToken($request)
    {
        $access_token = $this->getAccessToken($request);
        if(false === $access_token) return false;
        $user = \DB::table('oauth_sessions')
                            ->select(['oauth_sessions.id', 'oauth_sessions.client_id'])
                            ->join('oauth_access_tokens', 'oauth_access_tokens.session_id', '=', 'oauth_sessions.id')
                            ->where('oauth_access_tokens.id', $access_token)
                            ->first();
        return $user;
    }

    private function getAccessToken($request)
    {
        if ($request->headers->has('Authorization') === false) {
            return false;
        }

        $header = $request->headers->get('Authorization');

        if (substr($header, 0, 7) !== 'Bearer ') {
            return false;
        }

        return trim(substr($header, 7));
    }
}
