<?php

namespace Sergekukharev\PhpChefClient;

class DataBagItem
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, array $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public static function fromJson($name, $json)
    {
        return new static($name, json_decode($json, true));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->data);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function withElement($key, $value)
    {
        return new static($this->name, array_merge($this->data, [$key => $value]));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function get($key)
    {
        if (!$this->has($key)) {
            throw new \RuntimeException('Undefined index: ' . $key);
        }

        return $this->data[$key];
    }

    public function without($key)
    {
        $newData = $this->data;

        unset($newData[$key]);

        return new static($this->name, $newData);
    }
}
