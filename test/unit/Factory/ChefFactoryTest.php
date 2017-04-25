<?php

namespace Sergekukharev\PhpChefClient\Factory;


use Guzzle\Http\Client;

class ChefFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryUsesClientFactoryForGettingGuzzleClient()
    {
        $chef = ChefFactory::create(
            'example.com',
            'client-name',
            __DIR__ . '/../../fixture/file/testkey.pem',
            __DIR__
        );

        $client = $chef->getClient();

        self::assertInstanceOf(Client::class, $client);
        self::assertEquals('example.com', $client->getBaseUrl());
    }

    public function testFactoryUsesFilesystemCacheByDefault()
    {
        $cachePath = __DIR__;

        $chef = ChefFactory::create(
            'example.com',
            'client-name',
            __DIR__ . '/../../fixture/file/testkey.pem',
            $cachePath
        );

        self::assertTrue($chef->isCachingEnabled());
    }

    public function testFactoryCanCreateChefWithoutCache()
    {
        $chef = ChefFactory::createWithNoCaching('example.com', 'client-name', __DIR__ . '/../../fixture/file/testkey.pem');

        self::assertFalse($chef->isCachingEnabled());
    }
}
