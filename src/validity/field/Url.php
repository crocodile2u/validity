<?php

namespace validity\field;

class Url extends Str
{
    protected function castToType($value)
    {
        $value = parent::castToType($value);
        if ($value) {
            $value = filter_var($value, FILTER_VALIDATE_URL);
        }
        return $value ?: null;
    }
}