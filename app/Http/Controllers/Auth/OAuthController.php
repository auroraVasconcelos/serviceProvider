<?php

// app/Http/Controllers/Auth/OAuthController.php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client as HttpClient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    public function redirect()
    {
        $query = http_build_query([
            'client_id' => config('services.passport.client_id'),
            'redirect_uri' => config('services.passport.redirect_uri'),
            'response_type' => 'code',
            'scope' => '',
        ]);
        return redirect(config('services.passport.url') . '/oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $http = new HttpClient;

        $response = $http->post(config('services.passport.url') . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.passport.client_id'),
                'client_secret' => config('services.passport.client_secret'),
                'redirect_uri' => config('services.passport.redirect_uri'),
                'code' => $request->code,
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);

        // Retrieve user information from the token response if available
        $userInfo = $http->get(config('services.passport.url') . '/api/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $data['access_token'],
            ],
        ]);

        $userDetails = json_decode((string) $userInfo->getBody(), true);

        // Find or create the user in your application
        $user = User::updateOrCreate(
            ['email' => $userDetails['email']],
            [
                'name' => $userDetails['name'],
                'password' => bcrypt(Str::random(24)), // Random password since it won't be used
            ]
        );

        // Log the user in
        Auth::login($user);

        // Store access token in session or user record
        session(['access_token' => $data['access_token']]);

        return redirect('/');
    }
}

