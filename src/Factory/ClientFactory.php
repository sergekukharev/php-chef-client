<?php

namespace Sergekukharev\PhpChefClient\Factory;

use Guzzle\Http\Client;
use Guzzle\Http\ClientInterface;
use LeaseWeb\ChefGuzzle\Plugin\ChefAuth\ChefAuthPlugin;
use Webmozart\Assert\Assert;

class ClientFactory
{
    /**
     * @param string $baseUrl
     * @param string $clientName
     * @param string $keyPath
     * @return ClientInterface
     * @throws \Guzzle\Common\Exception\RuntimeException
     */
    public static function create($baseUrl, $clientName, $keyPath)
    {
        Assert::string($baseUrl);
        Assert::string($clientName);
        Assert::fileExists($keyPath);

        $chefAuthPlugin = new ChefAuthPlugin($clientName, $keyPath);

        $client = new Client($baseUrl);
        $client->addSubscriber($chefAuthPlugin);

        return $client;
    }
}
