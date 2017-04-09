<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class LandController extends Controller
{
    public function land(Request $request)
    {
        return view('twitter.land');
    }

    public function show(Request $request)
    {
		/**
		get tweets
		*/
		$twitterConfig = config('services.twitter');
		$token = $request->session()->get('twitter_access_token');
		$secret = $request->session()->get('twitter_access_secret');

		$oauth1 = new Oauth1([
                "consumer_key"    => $twitterConfig['client_id'],
                "consumer_secret" => $twitterConfig['client_secret'],
                "token"           => $token,
                "token_secret"    => $secret,
            ]);
		$stack = HandlerStack::create();
		$stack->push($oauth1);

		$client = new Client([
                'base_uri' => 'https://api.twitter.com/1.1/',
                'handler' => $stack,
                'auth' => 'oauth'
        	]);
		$response = $client->get('statuses/user_timeline.json');
		$tweets = json_decode($response->getBody());

		/**
	    call watson	
		*/
		$watsonConfig = config('services.watson');
		$watsonClient = new Client([
				'base_uri' => 'https://gateway.watsonplatform.net/personality-insights/api/v3/'
			]);
		
		$contentList = ['contentItems' => array_map(
												function($tweet) {
													return ['content'  => $tweet->text,
															'id'	   => $tweet->id,
															'created'  => strtotime($tweet->created_at),
															'language' => $tweet->lang,
															'reply'    => is_null($tweet->in_reply_to_status_id)
														];	
												}, $tweets)];
//TEST
//$contentList = json_decode(file_get_contents('/home/vagrant/contents.json'));

		try {
			$watsonResponse = $watsonClient->post('profile?version=2017-04-09', 
												['json' => $contentList,
											 	 'auth' => [$watsonConfig['username'], $watsonConfig['password']] 
												]); 
var_dump($watsonResponse->getBody());
		} catch (\Exception $e) {
var_dump($e->getResponse()->getBody()->getContents());
		}
		
        return view('twitter.show');
    }
}
