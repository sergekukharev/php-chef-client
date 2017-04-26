<?php

namespace Sergekukharev\PhpChefClient;

use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\Void\VoidCachePool;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Cache\CacheItemPoolInterface;

class ChefTest extends \PHPUnit_Framework_TestCase
{
    const DATA_BAG_ITEM_NAME = 'config';

    private static $dataBagItemContent = [
        'db_name' => 'test',
        'db_user' => 'test_user',
        'db_pass' => 'qwerty',
        'db_host' => 'localhost'
    ];

    private static $dataBagItemCached = [
        'db_name' => 'cache',
        'db_user' => 'cache',
        'db_pass' => 'cache',
        'db_host' => 'cache'
    ];

    const DATA_BAG_NAME = 'databag';

    /**
     * @var ClientInterface | PHPUnit_Framework_MockObject_MockObject
     */
    private $clientMock;

    /**
     * @var Chef
     */
    private $chef;

    /**
     * @var CacheItemPoolInterface | PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    public function setUp()
    {
        $this->clientMock = $this->getMockBuilder(ClientInterface::class)->getMock();
        $this->cacheMock = $this->getMockBuilder(VoidCachePool::class)->getMock();

        $this->chef = new Chef($this->clientMock, $this->cacheMock);
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
     * @param $dataBagName
     * @param $dataBagItemName
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

    public function testGetDataBagItemCachesRequest()
    {
        $uri = 'data/' . self::DATA_BAG_NAME . '/' . self::DATA_BAG_ITEM_NAME;
        $cacheKey = str_replace('/', Chef::CACHE_KEY_SPECIAL_CHAR_REPLACER, $uri);

        $this->cacheDoesntHaveItem($cacheKey);

        $this->cacheWillSaveItem();

        $this->prepareClientMock(
            $uri,
            'get',
            new Response(StatusCode::OK, [], json_encode(self::$dataBagItemContent))
        );

        $this->chef->getDataBagItem(self::DATA_BAG_NAME, self::DATA_BAG_ITEM_NAME);
    }

    /**
     * @param string $cacheKey
     */
    private function cacheDoesntHaveItem($cacheKey)
    {
        $this->cacheMock
            ->expects(self::once())
            ->method('hasItem')
            ->with($cacheKey)
            ->willReturn(false);
    }

    /**
     * @internal param string $cacheKey
     */
    private function cacheWillSaveItem()
    {
        $this->cacheMock
            ->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(CacheItem::class));
    }

    public function testGetDataBagItemWillReturnCachedRequestFirst()
    {
        $uri = 'data/' . self::DATA_BAG_NAME . '/' . self::DATA_BAG_ITEM_NAME;
        $cacheKey = str_replace('/', Chef::CACHE_KEY_SPECIAL_CHAR_REPLACER, $uri);
        $cachedResponse = new Response(StatusCode::OK, [], json_encode(self::$dataBagItemCached));

        $this->cacheHasItem($cacheKey);
        $this->cacheWillReturnValue($cacheKey, $cachedResponse);

        $dataBagItem = $this->chef->getDataBagItem(self::DATA_BAG_NAME, self::DATA_BAG_ITEM_NAME);
        self::assertEquals(
            DataBagItem::fromJson(self::DATA_BAG_ITEM_NAME, json_encode(self::$dataBagItemCached)),
            $dataBagItem
        );

        self::assertNotEquals(
            DataBagItem::fromJson(self::DATA_BAG_ITEM_NAME, json_encode(self::$dataBagItemContent)),
            $dataBagItem
        );
    }

    private function cacheHasItem($cacheKey)
    {
        $this->cacheMock
            ->expects(self::once())
            ->method('hasItem')
            ->with($cacheKey)
            ->willReturn(true);
    }

    private function cacheWillReturnValue($cacheKey, $value)
    {
        $this->cacheMock
            ->expects(self::once())
            ->method('getItem')
            ->with($cacheKey)
            ->willReturn(new CacheItem($cacheKey, true, $value));
    }

    public function testCanProvideClient()
    {
        self::assertSame($this->clientMock, $this->chef->getClient());
    }

    public function testCanTellIfCachingIsEnabled()
    {
        self::assertTrue($this->chef->isCachingEnabled());
    }

    public function testCanTellIfCachingIsDisabled()
    {
        $chefWithoutCache = new Chef($this->clientMock, null);
        self::assertFalse($chefWithoutCache->isCachingEnabled());
    }
}
