<?php

namespace validity\test\field;

use validity\Field;
use validity\test\NumberTest;

require_once __DIR__ . "/../NumberTest.php";

class IntegerTest extends NumberTest
{
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
            ["-1000", true, -1000, "signed valid integer fails validation"],
            ["+1000", true, 1000, "signed valid integer fails validation"],
            ["0001234", true, 1234, "valid integer fails validation"],
            ["0", true, 0, "valid integer fails validation"],
        ];
    }
    function provider_testRangeValidation()
    {
        return [
            [100, 200, true, '100', true, 100, "valid integer fails validation"],
            [100, 200, true, '200', true, 200, "valid integer fails validation"],
            [100, 200, true, '150', true, 150, "valid integer fails validation"],
            [100, 200, true, '1500', false, null, "out-of-bounds integer value passes validation"],
            [100, 200, false, '100', false, null, "out-of-bounds integer (exclusive range) fails validation"],
            [100, 200, false, '200', false, null, "out-of-bounds integer (exclusive range) fails validation"],
        ];
    }
    protected function createField(): Field
    {
        return Field::int("key");
    }
}