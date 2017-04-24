<?php

namespace Sergekukharev\PhpChefClient\Factory;


use Guzzle\Http\Client;
use Guzzle\Http\ClientInterface;
use InvalidArgumentException;
use LeaseWeb\ChefGuzzle\Plugin\ChefAuth\ChefAuthPlugin;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    const CHEF_SERVER_URL = 'example-chef-server.com';

    const CLIENT_NAME = 'test-client';

    /**
     * @var string
     */
    private static $keyLocation;

    public static function setUpBeforeClass()
    {
        self::$keyLocation = __DIR__ . '/../../fixture/file/testkey.pem';
    }

    public function testCreatesGuzzleClient()
    {
        self::assertInstanceOf(Client::class, $this->createClientInstance());
    }

    public function testAttachesChefAuthPlugin()
    {
        $client = $this->createClientInstance();

        self::assertTrue($this->wasAuthPluginAttached($client));
    }

    public function testAssignsBaseUrl()
    {
        $client = $this->createClientInstance();

        self::assertEquals(self::CHEF_SERVER_URL, $client->getBaseUrl());
    }

    public function testChefAuthPluginIsCorrectlyConfigured()
    {
        $client = $this->createClientInstance();

        $plugin = $this->getPluginFromClient($client);

        self::assertEquals(self::CLIENT_NAME, $plugin->getClientName());
        self::assertEquals(self::$keyLocation, $plugin->getKeylocation());
    }

    public function testBaseUrlShouldBeString()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        ClientFactory::create(null, self::CLIENT_NAME, self::$keyLocation);
    }

    public function testClientNameShouldBeString()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        ClientFactory::create(self::CHEF_SERVER_URL, null, self::$keyLocation);
    }

    public function testKeyLocationShouldBeExistingFile()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        ClientFactory::create(self::CHEF_SERVER_URL, self::CLIENT_NAME, __DIR__ . '/non/existing/path');
    }

    /**
     * @return \Guzzle\Http\ClientInterface
     */
    private function createClientInstance()
    {
        return ClientFactory::create(self::CHEF_SERVER_URL, self::CLIENT_NAME, self::$keyLocation);
    }

    /**
     * @param ClientInterface $client
     * @return bool
     */
    private function wasAuthPluginAttached(ClientInterface $client)
    {
        return !is_null($this->getPluginFromClient($client));
    }
    /**
     * @param ClientInterface $client
     * @return ChefAuthPlugin
     */
    private function getPluginFromClient(ClientInterface $client)
    {
        $listeners = $client->getEventDispatcher()->getListeners()['request.before_send'] ;

        $plugin = null;
        /** @var array $listeners */
        foreach ($listeners as $listener) {
            if ($listener[0] instanceof ChefAuthPlugin) {
                $plugin = $listener[0];
            }
        }

        return $plugin;
    }
}
