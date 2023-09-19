<?php

namespace App\Modules\Client\OAuth\Events;

use App\Modules\Client\OAuth\Token\TokenInterface;

class RetrievedRequestToken
{
    public function __construct(public TokenInterface $token)
    {
    }
}
