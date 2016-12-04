<?php

namespace validity\field;

use validity\Field;

interface RangeAware
{
    /**
     * @param $min
     * @param null $message
     * @return $this
     */
    public function setMin($min, $message = null): Field;
    /**
     * @param $max
     * @param null $message
     * @return $this
     */
    public function setMax($max, $message = null): Field;
}