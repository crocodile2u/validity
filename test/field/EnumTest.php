<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

class EnumTest extends BaseFieldTest
{
    function testValidation()
    {
        $enum = array(1, false, 'asdf');
        $field = Field::enum('key', $enum);
        foreach ($enum as $valid_value) {
            $this->assertTrue(
                $field->isValid($valid_value),
                __METHOD__ . ": enum validation does not accept valid value"
            );
        }

        $this->assertFalse(
            $field->isValid('invalid enum value'),
            __METHOD__ . ": enum validation must not accept invalid value"
        );

        $field = Field::enum('key', [0, 1])->setRequired();
        $this->assertTrue($field->isValid('0'), __METHOD__ . ": zero value fails enum validation");
    }
}