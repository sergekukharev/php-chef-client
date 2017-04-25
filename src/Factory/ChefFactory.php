<?php

namespace Sergekukharev\PhpChefClient\Factory;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use Guzzle\Http\ClientInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Sergekukharev\PhpChefClient\Chef;

class ChefFactory
{
    /**
     * @param string $baseUrl
     * @param string $clientName
     * @param string $keyPath
     * @return Chef
     * @throws \LogicException
     * @throws \Guzzle\Common\Exception\RuntimeException
     */
    public static function create($baseUrl, $clientName, $keyPath, $cacheStoragePath)
    {
        $filesystemAdapter = new Local($cacheStoragePath);
        $filesystem        = new Filesystem($filesystemAdapter);

        $cacheAdapter = new FilesystemCachePool($filesystem);

        $client = ClientFactory::create($baseUrl, $clientName, $keyPath);

        return new Chef($client, $cacheAdapter);
    }

    public static function createWithNoCaching($baseUrl, $clientName, $keyPath)
    {
        $client = ClientFactory::create($baseUrl, $clientName, $keyPath);

        return new Chef($client);
    }
}
