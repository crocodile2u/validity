<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

class DateTimeTest extends BaseFieldTest
{
    function testValidDate()
    {
        $this->assertValid(["key" => "2010-01-01 12:23:23"], Field::datetime("key"), "2010-01-01 12:23:23");
    }
    function testValidDateWithCustomFormat()
    {
        $field = Field::datetime("key")->setInputFormat("d/m/Y H:i")->setOutputFormat("d.m.Y \a\\t H:i");
        $this->assertValid(["key" => "01/01/2010 12:20"], $field, "01.01.2010 at 12:20");
    }
    function testNonStrictInputMode()
    {
        $field = Field::datetime("key")->setInputFormat("d/m/Y", false);
        $this->assertValid(["key" => "2010-01-01"], $field, "2010-01-01 00:00:00");
    }
    function testInvalidDates()
    {
        $this->assertInvalid(["key" => " "], Field::datetime("key")->setRequired(), "");
        $this->assertInvalid(["key" => "invalid date"], Field::datetime("key"));
        $this->assertInvalid(["key" => "2001-02-30 12:09"], Field::datetime("key"));
        $this->assertInvalid(["key" => "2001-02-28 12:60"], Field::datetime("key"));
    }
    function testRangeValidation()
    {
        $field = Field::datetime("key")
            ->setInputFormat("d.m.Y H:i:s")
            ->setOutputFormat("Y-m-d H:i:s")
            ->setMin("2010-01-01 12:00:00")
            ->setMax("2010-01-31 11:59:59");
        $this->assertValid(["key" => "01.01.2010 12:00:00"], $field, "2010-01-01 12:00:00");
        $this->assertValid(["key" => "31.01.2010 11:59:59"], $field, "2010-01-31 11:59:59");
        $this->assertValid(["key" => "20.01.2010 14:12:45"], $field, "2010-01-20 14:12:45");
        $this->assertInvalid(["key" => "01.01.2010 11:59:59"], $field);
        $this->assertInvalid(["key" => "31.01.2010 12:00:00"], $field);
    }
}