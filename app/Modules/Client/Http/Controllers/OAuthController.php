<?php

namespace App\Modules\Client\Http\Controllers;

use App\Modules\Client\Http\Requests\GoogleAuthenticateRequest;
use App\Modules\Client\Models\Integration;
use App\Modules\Client\Services\GooglePhotos;
use App\Modules\Core\Services\States;
use Google\Client;
use Illuminate\Routing\Controller;

class OAuthController extends Controller
{
    public function google(GoogleAuthenticateRequest $request)
    {
        $integration = Integration::where('service', GooglePhotos::SERVICE_NAME)
            ->first();

        $client = new Client();
        $client->setClientId($integration->key);
        $client->setClientSecret($integration->secret);
        $client->addScope(GooglePhotos::GOOGLE_PHOTOS_SCOPES);
        $client->setRedirectUri(route('client.oauth.google'));
        $client->setAccessType('offline');        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth

        $client->fetchAccessTokenWithAuthCode($request->input('code'));
        $access_token = $client->getAccessToken();

        $integration->update([
            'refresh_token' => $access_token['refresh_token'],
            'state_code' => States::STATE_COMPLETED
        ]);
    }
}
