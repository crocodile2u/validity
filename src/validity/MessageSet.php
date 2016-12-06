<?php

namespace validity;

class MessageSet extends \ArrayObject
{
    function add($field, $message, $key = null)
    {
        $this[$field][] = new Message($message, $key);
        return null;
    }
    function reset($field)
    {
        if ($this->offsetExists($field)) {
            $this->offsetUnset($field);
        }
    }
    function export()
    {
        return $this->getArrayCopy();
    }
    function toPlainArray($separator = "; ")
    {
        return array_map(
            function(array $list) use ($separator) {
                return join($separator, $list);
            },
            $this->export()
        );
    }
    function get($field)
    {
        return isset($this[$field]) ? $this[$field] : null;
    }
    function getAsString($field, $separator = "; ")
    {
        $messages = $this->get($field);
        if ($messages) {
            return join($separator, $messages);
        } else {
            return null;
        }
    }
    function toString($separator = "\n", $innerSeparator = "; ")
    {
        return join($separator, $this->toPlainArray($innerSeparator));
    }
}