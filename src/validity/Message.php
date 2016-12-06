<?php

namespace validity;

class Message implements \JsonSerializable
{
    /**
     * @var string
     */
    private $text;
    /**
     * @var null
     */
    private $key;

    /**
     * Message constructor.
     * @param string $text
     * @param null $key
     */
    function __construct(string $text, $key = null)
    {
        $this->text = $text;
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->text;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return [
            "text" => $this->text,
            "key" => $this->key,
        ];
    }
}