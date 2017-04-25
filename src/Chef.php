<?php

namespace Sergekukharev\PhpChefClient;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Webmozart\Assert\Assert;

class Chef
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
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

        $response = $this->client->get('data/' . $dataBagName . '/' . $dataBagItemName)->send();

        return DataBagItem::fromJson($dataBagItemName, $response->getBody(true));
    }
}
