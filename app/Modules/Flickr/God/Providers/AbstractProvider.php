<?php

namespace App\Modules\Flickr\God\Providers;

use App\Modules\Core\God\Providers\AbstractProvider as CoreAbstractProvider;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\God\Providers\Interfaces\HasMocking;
use GuzzleHttp\Psr7\Response;
use Mockery\MockInterface;

abstract class AbstractProvider extends CoreAbstractProvider implements HasMocking
{
    public const NSID = '94529704@N02';
    public const NSID_WITH_MULTI_PHOTOS_PAGES = '73115043@N07';

    public const NSID_USER_NOT_FOUND = '64994773@N03';

    public const PHOTOSET_ID = 72157674594210788;

    public const DEFAULT_CONTENT_TYPE = [
        'Content-Type' => 'application/json; charset=utf-8',
    ];
    protected MockInterface $mock;

    public function setMocking(MockInterface $mock): self
    {
        $this->mock = $mock;

        return $this;
    }

    public function getMocking(): MockInterface
    {
        return $this->mock;
    }

    protected function error(int $code, string $message): string
    {
        return json_encode([
            'stat' => 'fail',
            'code' => $code,
            'message' => $message,
        ]);
    }

    protected function success(string $fixtureFile): Response
    {
        return new Response(
            200,
            self::DEFAULT_CONTENT_TYPE,
            $this->getFixture($fixtureFile)
        );
    }

    protected function exception(string $message = 'Flickr error'): FailedException
    {
        return new FailedException($message);
    }
}
