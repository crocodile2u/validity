<?php

namespace validity\test;

use PHPUnit\Framework\TestCase;
use validity\Field;

abstract class BaseFieldTest extends TestCase
{
    protected function assertValid($data, Field $field, $filtered, $message = null)
    {
        $result = $field->isValid($data);
        if (!$result) {
            $message .= ": " . $field->getOwnerFieldSet()->getErrors()->toString();
        }
        $this->assertTrue($field->isValid($data), $message);
        $this->assertEquals($filtered, $field->getFiltered(), "Filtered value is incorrect");
    }
    protected function assertInvalid($data, Field $field, $filtered = null, $message = null)
    {
        $this->assertFalse($field->isValid($data), $message);
        $this->assertSame($filtered, $field->getFiltered(), "Filtered value should be NULL");
    }
}