<?php

namespace validity\test;

use validity\Field;
use validity\Report;

abstract class BaseFieldTest extends \PHPUnit_Framework_TestCase
{
    protected function assertValid($data, Field $field, $filtered, $message = null)
    {
        $report = new Report($data);
        $this->assertTrue($field->isValid($report), $message);
        $this->assertEquals($filtered, $report->getFiltered($field->getName()), "Filtered value is incorrect");
    }
    protected function assertInvalid($data, Field $field, $message = null)
    {
        $report = new Report($data);
        $this->assertFalse($field->isValid($report), $message);
        $this->assertNull($report->getFiltered($field->getName()), "Filtered value should be NULL");
    }
}