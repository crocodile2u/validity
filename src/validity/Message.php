<?php

namespace validity;

class Message implements \JsonSerializable
{
    private $text;
    private $key;
    function __construct(string $text, $key = null)
    {
        $this->text = $text;
        $this->key = $key;
    }
    function __toString()
    {
        return $this->text;
    }
    function jsonSerialize()
    {
        return [
            "text" => $this->text,
            "key" => $this->key,
        ];
    }
}