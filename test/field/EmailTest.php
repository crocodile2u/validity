<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

class EmailTest extends BaseFieldTest
{
    function testValidation()
    {
        $field = Field::email();
        $this->assertFalse($field->isValid('invalid email'), __METHOD__ . ": invalid email passes validation");
        $this->assertTrue($field->isValid('valid@email.com'), __METHOD__ . ": valid email fails validation");
    }
}