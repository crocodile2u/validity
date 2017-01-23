<?php

namespace validity\field;

use validity\Language;

class Email extends Str
{
    /**
     * Email constructor.
     * @param string $name
     * @param string $message
     */
    protected function __construct(string $name = null, $message = null)
    {
        parent::__construct($name, $message);
        $this->addRegexpRule(
            "~^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}\~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$~",
            $message,
            Language::EMAIL_EXPECTED
        );
    }
}