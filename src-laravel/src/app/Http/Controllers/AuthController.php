<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class AuthController extends Controller
{
    public function profile()
    {
        /**@var \Illuminate\Auth\GenericUser $user */
        $user = Auth::user();

        if (!Cache::has('key')) {
            $response = [
                'code' => 'get_my_profile',
                'message' => 'Get My Profile',
                'data' => [
                    'id' => $user->getAuthIdentifier(),
                    'name' => $user->__get('name'),
                    'role' => $user->__get('roles'),
                    'time' => Carbon::now()
                ]
            ];
            Cache::put('key', $response, 5);
        } else {
            $response = Cache::get('key');
        }
        return response($response);
    }
}
