<?php

namespace Tests;

use App\Services\Client\CrawlerClientResponse;
use App\Services\Client\Domain\ResponseInterface;
use App\Services\Client\XCrawlerClient;
use App\Services\Crawler\OnejavCrawler;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

abstract class AbstractCrawlingTest extends TestCase
{
    protected MockObject|XCrawlerClient $mocker;
    protected string $fixtures;

    public function setUp(): void
    {
        parent::setUp();
        app()->bind(ResponseInterface::class, CrawlerClientResponse::class);
        $this->mocker = $this->getMockBuilder(XCrawlerClient::class)->getMock();
        $this->mocker->method('init')->willReturnSelf();
        $this->mocker->method('setHeaders')->willReturnSelf();
        $this->mocker->method('setContentType')->willReturnSelf();
    }

    public function loadSucceed(string $mockFile, $crawler)
    {
        $this->mocker->method('get')->willReturn($this->getSuccessfulMockedResponse($mockFile));
        app()->instance(XCrawlerClient::class, $this->mocker);
        return app($crawler);
    }
}
