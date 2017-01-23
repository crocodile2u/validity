<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

class PhoneTest extends BaseFieldTest
{
    function testValidation()
    {
        $field = Field::phone();
        $this->assertFalse($field->isValid('+7 888 (45 12345 3456 invalid phone'), __METHOD__ . ": invalid phone passes validation");
        $this->assertTrue($field->isValid('+7 (123 456-78-90'), __METHOD__ . ": valid phone fails validation");
    }
}