<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

require_once __DIR__ . "/../BaseFieldTest.php";

class IntTest extends BaseFieldTest
{
    /**
     * @param mixed $input
     * @param bool $isValid
     * @param int $filtered
     * @param string $message
     * @dataProvider provider_testIsValid
     */
    function testIsValid($input, $isValid, $filtered, $message)
    {
        $field = Field::int("key");
        if ($isValid) {
            $this->assertValid(["key" => $input], $field, $filtered, $message);
        } else {
            $this->assertInvalid(["key" => $input], $field, $message);
        }
    }
    function provider_testIsValid()
    {
        return [
            ["", true, "", "optional empty string fails integer validation"],
            ["10", true, 10, "valid integer fails validation"],
            ["  10 ", false, null, "integer surrounded by spaces passes validation"],
            ["asdf", false, null, "string passes integer validation"],
            ["13.23", false, null, "string containing a floating-point-number passes integer validation"],
            ["2e10", false, null, "number with exponential part passes integer validation"],
            ["0b001010", false, null, "binary notation passes integer validation"],
            ["0xFF", false, null, "hexadecimal notation passes integer validation"],
            ["9223372036854775810", false, null, "too big integer passes integer validation"],
            ["-9223372036854775810", false, null, "too big negative integer passes integer validation"],
        ];
    }

    /**
     * @param int $min
     * @param int $max
     * @param mixed $value
     * @param bool $isValid
     * @param int $filtered
     * @param string $message
     * @dataProvider provider_testRangeValidation
     */
    function testRangeValidation($min, $max, $value, $isValid, $filtered, $message)
    {
        $field = Field::int("key")->setMin($min)->setMax($max);
        if ($isValid) {
            $this->assertValid(["key" => $value], $field, $filtered, $message);
        } else {
            $this->assertInvalid(["key" => $value], $field, $message);
        }
    }
    function provider_testRangeValidation()
    {
        return [
            [100, 200, '100', true, 100, "valid integer fails validation"],
            [100, 200, '200', true, 200, "valid integer fails validation"],
            [100, 200, '150', true, 150, "valid integer fails validation"],
            [100, 200, '1500', false, null, "out-of-bounds integer value passes validation"],
        ];
    }
}