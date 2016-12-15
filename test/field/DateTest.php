<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

class DateTest extends BaseFieldTest
{
    function testValidDate()
    {
        $this->assertValid("2010-01-01", Field::date("key"), "2010-01-01");
    }
    function testValidDateWithCustomFormat()
    {
        $field = Field::date("key")->setInputFormat("d/m/Y")->setOutputFormat("d.m.Y");
        $this->assertValid("01/01/2010", $field, "01.01.2010");
    }
    function testNonStrictInputMode()
    {
        $field = Field::date("key")->setInputFormat("d/m/Y", false);
        $this->assertValid("2010-01-01", $field, "2010-01-01");
    }
    function testInvalidDates()
    {
        $this->assertInvalid(" ", Field::date("key")->setRequired(), "");
        $this->assertInvalid("invalid date", Field::date("key"));
        $this->assertInvalid("2001-02-30", Field::date("key"));
    }
    function testRangeValidation()
    {
        $field = Field::date("key")
            ->setInputFormat("d.m.Y")
            ->setOutputFormat("Y-m-d")
            ->setMin("2010-01-01")
            ->setMax("2010-01-31");
        $this->assertValid("01.01.2010", $field, "2010-01-01");
        $this->assertValid("31.01.2010", $field, "2010-01-31");
        $this->assertValid("20.01.2010", $field, "2010-01-20");
        $this->assertInvalid("31.12.2009", $field);
        $this->assertInvalid("01.02.2010", $field);
    }
}