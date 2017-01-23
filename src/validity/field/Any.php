<?php

namespace validity\field;

use validity\Field;

class Any extends Field
{
    /**
     * Any constructor.
     * @param string $name
     */
    protected function __construct(string $name = null)
    {
        parent::__construct($name, null);
    }

    /**
     * @return bool
     */
    protected function allowsArray()
    {
        return true;
    }
}