<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

class DateTest extends BaseFieldTest
{
    /**
     * @param string $input
     * @param string $filtered
     * @dataProvider provider_testValidDates
     */
    function testValidDates($input, $filtered)
    {
        $this->assertValid(["key" => $input], Field::date("key"), $filtered);
    }
    function provider_testValidDates()
    {
        return [
            ["+1year", date("Y-m-d", strtotime("+1year"))],
            ["-1year", date("Y-m-d", strtotime("-1year"))],
            ["2010-01-01", "2010-01-01"],
            ["2010-06", "2010-06-01"],
            ["06/05/2010", "2010-06-05"],
        ];
    }
    function testInvalidDates()
    {
        $this->assertInvalid(["key" => " "], Field::date("key"));
    }
}