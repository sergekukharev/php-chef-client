<?php

namespace Sergekukharev\PhpChefClient;

use Guzzle\Http\ClientInterface;
use PHPUnit_Framework_MockObject_MockObject;

class ChefTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientInterface | PHPUnit_Framework_MockObject_MockObject
     */
    private $clientMock;

    public function setUp()
    {
        $this->clientMock = $this->getMockBuilder(ClientInterface::class)->getMock();
    }

    public function testCreation()
    {
        $chef = new Chef($this->clientMock);
    }
}
