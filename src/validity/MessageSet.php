<?php

namespace validity;

class MessageSet extends \ArrayObject implements \JsonSerializable
{
    /**
     * @param string $field
     * @param string $message
     * @param null $key
     * @return null
     */
    function add(string $field, string $message, $key = null)
    {
        $this[$field][] = new Message($message, $key);
        return null;
    }

    /**
     * @param string $field
     */
    function reset(string $field)
    {
        if ($this->offsetExists($field)) {
            $this->offsetUnset($field);
        }
    }

    /**
     * @return array
     */
    function export()
    {
        return $this->getArrayCopy();
    }

    /**
     * @param string $separator
     * @return array
     */
    function toPlainArray($separator = "; ")
    {
        return array_map(
            function(array $list) use ($separator) {
                return join($separator, $list);
            },
            $this->export()
        );
    }

    /**
     * @param string $field
     * @return mixed|null
     */
    function get($field)
    {
        return isset($this[$field]) ? $this[$field] : null;
    }

    /**
     * @param string $field
     * @param string $separator
     * @return null|string
     */
    function getAsString($field, $separator = "; ")
    {
        $messages = $this->get($field);
        if ($messages) {
            return join($separator, $messages);
        } else {
            return null;
        }
    }

    /**
     * @param string $separator
     * @param string $innerSeparator
     * @return string
     */
    function toString(string $separator = "\n", string $innerSeparator = "; ")
    {
        return join($separator, $this->toPlainArray($innerSeparator));
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return $this->export();
    }
}