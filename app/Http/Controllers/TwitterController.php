<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;

class TwitterController extends Controller
{

    /**
     * Redirect the user to the Twitter authentication page.
     *
     * @return Response
     */
    public function redirectProvider()
    {
        return Socialite::driver('twitter')->redirect();
    }

    /**
     * Obtain the user information from Twitter.
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        try {
            $user = Socialite::driver('twitter')->user();
        } catch (Exception $e) {
            return redirect('auth/twitter');
        }
		$request->session()->put('twitter_access_token', $user->token);
		$request->session()->put('twitter_access_secret', $user->tokenSecret);
        return redirect('twitter');
    }
}
