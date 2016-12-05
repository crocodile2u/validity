<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

class DateTest extends BaseFieldTest
{
    function testValidDate()
    {
        $this->assertValid(["key" => "01.01.2010"], Field::date("key"), "2010-01-01");
    }
    function testValidDateWithCustomFormat()
    {
        $field = Field::date("key")->setInputFormat("d/m/Y")->setOutputFormat("d.m.Y");
        $this->assertValid(["key" => "01/01/2010"], $field, "01.01.2010");
    }
    function testNonStrictInputMode()
    {
        $field = Field::date("key")->setInputFormat("d/m/Y", false);
        $this->assertValid(["key" => "2010-01-01"], $field, "2010-01-01");
    }
    function testInvalidDates()
    {
        $this->assertInvalid(["key" => " "], Field::date("key")->setRequired(), "");
        $this->assertInvalid(["key" => "invalid date"], Field::date("key"));
        $this->assertInvalid(["key" => "2001-02-30"], Field::date("key"));
    }
    function testRangeValidation()
    {
        $field = Field::date("key")
            ->setInputFormat("d.m.Y")
            ->setOutputFormat("Y-m-d")
            ->setMin("2010-01-01")
            ->setMax("2010-01-31");
        $this->assertValid(["key" => "01.01.2010"], $field, "2010-01-01");
        $this->assertValid(["key" => "31.01.2010"], $field, "2010-01-31");
        $this->assertValid(["key" => "20.01.2010"], $field, "2010-01-20");
        $this->assertInvalid(["key" => "31.12.2009"], $field);
        $this->assertInvalid(["key" => "01.02.2010"], $field);
    }
}