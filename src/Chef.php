<?php

namespace Sergekukharev\PhpChefClient;

use Cache\Adapter\Void\VoidCachePool;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Psr\Cache\CacheItemPoolInterface;
use Webmozart\Assert\Assert;

class Chef
{
    const CACHE_KEY_SPECIAL_CHAR_REPLACER = '___';
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var CacheItemPoolInterface|null
     */
    private $cacheAdapter;

    public function __construct(ClientInterface $client, CacheItemPoolInterface $cacheAdapter = null)
    {
        $this->client = $client;
        $this->cacheAdapter = $cacheAdapter === null ? new VoidCachePool() : $cacheAdapter;
    }

    /**
     * @param string $dataBagName
     * @param string $dataBagItemName
     * @return DataBagItem
     * @throws \Guzzle\Http\Exception\RequestException
     * @throws ClientErrorResponseException 401 Unauthorized.
     * @throws ClientErrorResponseException 403 Forbidden.
     * @throws ClientErrorResponseException 404 Not Found.
     */
    public function getDataBagItem($dataBagName, $dataBagItemName)
    {
        Assert::stringNotEmpty($dataBagName);
        Assert::stringNotEmpty($dataBagItemName);

        $uri = 'data/' . $dataBagName . '/' . $dataBagItemName;

        $response = $this->sendCachedGetRequest($uri);

        return DataBagItem::fromJson($dataBagItemName, $response->getBody(true));
    }

    /**
     * @param string $uri
     * @return \Guzzle\Http\Message\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Guzzle\Http\Exception\RequestException
     */
    private function sendCachedGetRequest($uri)
    {
        $cacheKey = str_replace('/', self::CACHE_KEY_SPECIAL_CHAR_REPLACER, $uri);

        if ($this->cacheAdapter->has($cacheKey)) {
            return $this->cacheAdapter->get($cacheKey);
        }

        $response = $this->client->get($uri)->send();
        $this->cacheAdapter->set($cacheKey, $response);

        return $response;
    }
}
