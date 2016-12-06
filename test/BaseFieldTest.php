<?php

namespace validity\test;

use validity\Field;

abstract class BaseFieldTest extends \PHPUnit_Framework_TestCase
{
    protected function assertValid($data, Field $field, $filtered, $message = null)
    {
        $this->assertTrue($field->isValid($data), $message);
        $this->assertEquals($filtered, $field->getFiltered(), "Filtered value is incorrect");
    }
    protected function assertInvalid($data, Field $field, $filtered = null, $message = null)
    {
        $this->assertFalse($field->isValid($data), $message);
        $this->assertSame($filtered, $field->getFiltered(), "Filtered value should be NULL");
    }
}