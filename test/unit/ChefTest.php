<?php

namespace Sergekukharev\PhpChefClient;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;

class ChefTest extends \PHPUnit_Framework_TestCase
{
    const DATA_BAG_ITEM_NAME = 'config';

    private static $dataBagItemContent = [
        'db_name' => 'test',
        'db_user' => 'test_user',
        'db_pass' => 'qwerty',
        'db_host' => 'localhost'
    ];
    const DATA_BAG_NAME = 'databag';

    /**
     * @var ClientInterface | PHPUnit_Framework_MockObject_MockObject
     */
    private $clientMock;

    /**
     * @var RequestInterface | PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Chef
     */
    private $chef;

    public function setUp()
    {
        $this->clientMock = $this->getMockBuilder(ClientInterface::class)->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)->getMock();

        $this->chef = new Chef($this->clientMock);
    }

    public function testGetDataBagItemReturnsValidItem()
    {
        $this->prepareClientMock(
            'data/' . self::DATA_BAG_NAME . '/' . self::DATA_BAG_ITEM_NAME,
            'get',
            new Response(StatusCode::OK, [], json_encode(self::$dataBagItemContent))
        );

        $dataBagItem = $this->chef->getDataBagItem(self::DATA_BAG_NAME, self::DATA_BAG_ITEM_NAME);

        $expectedDataBag = new DataBagItem(self::DATA_BAG_ITEM_NAME, self::$dataBagItemContent);

        self::assertEquals($expectedDataBag, $dataBagItem);
    }

    private function prepareClientMock($expectedUrl, $expectedMethod, $responseToReturn) {
        $request = (new Request($expectedMethod, $expectedUrl))->setClient($this->clientMock);

        $this->clientMock
            ->expects(self::once())
            ->method($expectedMethod)
            ->with($expectedUrl)
            ->willReturn($request);

        $this->clientMock
            ->expects(self::once())
            ->method('send')
            ->willReturn($responseToReturn);
    }

    /**
     * @dataProvider provideGetDataBagItemInvalidInput
     */
    public function testGetDataBagItemChecksDataBagNameAndDataBagItemName($dataBagName, $dataBagItemName)
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $this->chef->getDataBagItem($dataBagName, $dataBagItemName);
    }

    public function provideGetDataBagItemInvalidInput()
    {
        return [
            ['', self::DATA_BAG_ITEM_NAME],
            [123, self::DATA_BAG_ITEM_NAME],
            [null, self::DATA_BAG_ITEM_NAME],
            [self::DATA_BAG_NAME, ''],
            [self::DATA_BAG_NAME, 123],
            [self::DATA_BAG_NAME, null],
        ];
    }
}
