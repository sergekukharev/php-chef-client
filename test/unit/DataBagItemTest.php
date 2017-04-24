<?php

namespace Sergekukharev\PhpChefClient;


class DataBagItemTest extends \PHPUnit_Framework_TestCase
{
    const DATABAG_NAME = 'test-databag';
    private static $dataBagContents = [
        'test-key' => 'test-value',
        'foo' => 'bar',
        'nested' => [
            'faz' => 'baz'
        ]
    ];

    public function testCanBeCreatedFromArray()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        self::assertFalse($databag->isEmpty());
        self::assertEquals(self::DATABAG_NAME, $databag->getName());
    }

    public function testIfCreatedFromEmptyArrayWillBeEmpty()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, []);

        self::assertTrue($databag->isEmpty());
    }

    public function testCanBePresentedAsJson()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        self::assertJsonStringEqualsJsonString(json_encode(self::$dataBagContents), $databag->toJson());
    }

    public function testCanDetectElementsInside()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        self::assertTrue($databag->has('test-key'));
    }

    public function testCanReturnSingleElement()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        self::assertEquals(self::$dataBagContents['test-key'], $databag->get('test-key'));
    }

    public function testThrowsExceptionIfElementNotFound()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        $this->setExpectedException(\RuntimeException::class);

        $databag->get('unexistent-key');
    }

    public function testCanCreateNewInstaceWithNewElements()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        $newDatabag = $databag->withElement('new-key', 'new-value');

        self::assertEquals('new-value', $newDatabag->get('new-key'));
    }

    public function testIsImmutableWhenAddingElements()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        $newDatabag = $databag->withElement('new-key', 'new-value');

        self::assertNotSame($databag, $newDatabag);
    }

    public function testCanCreateNewInstanceWithoutSomeElements()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        $newDatabag = $databag->without('test-key');

        self::assertFalse($newDatabag->has('test-key'));
    }

    public function testNothingWrongHappensWhenTriesToRemoveNonExistentElement()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        $newDatabag = $databag->without('unknown-key');

        self::assertFalse($newDatabag->has('unknown-key'));
    }

    public function testIsImmutableWhenRemovingElements()
    {
        $databag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        $newDatabag = $databag->without('test-key');

        self::assertNotSame($databag, $newDatabag);
    }

    public function testHasFactoryMethodFromJson()
    {
        $databag = DataBagItem::fromJson(self::DATABAG_NAME, json_encode(self::$dataBagContents));
        $normallyCreatedDatabag = new DataBagItem(self::DATABAG_NAME, self::$dataBagContents);

        self::assertEquals($databag, $normallyCreatedDatabag);
        self::assertNotSame($databag, $normallyCreatedDatabag);
    }
}
