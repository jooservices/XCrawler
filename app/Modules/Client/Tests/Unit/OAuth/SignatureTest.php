<?php

namespace App\Modules\Client\Tests\Unit\OAuth;

use App\Modules\Client\OAuth\Credentials\Credentials;
use App\Modules\Client\OAuth\OAuth1\Signature\Signature;
use App\Modules\Client\OAuth\Uri\Uri;
use Tests\TestCase;

class SignatureTest extends TestCase
{
    public function testSignature()
    {
        $credentials = new Credentials(
            'consumerId',
            'consumerSecret',
            'callbackUrl'
        );

        $signature = new Signature($credentials);

        $this->assertEquals(
            '5oB24xwdHMlvXcyXvtT78sAsLQc=',
            $signature->getSignature(
                new Uri('https://www.flickr.com/services/oauth/request_token'),
                [
                    'oauth_nonce' => '1234567890',
                    'oauth_timestamp' => '1234567890',
                    'oauth_consumer_key' => 'consumerId',
                    'oauth_signature_method' => 'HMAC-SHA1',
                    'oauth_version' => '1.0',
                    'oauth_callback' => 'callbackUrl',
                ],
            )
        );
    }
}
