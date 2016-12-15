<?php

namespace validity\test\field;

use validity\Field;
use validity\test\NumberTest;

require_once __DIR__ . "/../NumberTest.php";

class DoubleTest extends NumberTest
{
    function provider_testIsValid()
    {
        return [
            ["", true, "", "optional empty string fails float validation"],
            ["10", true, 10, "valid float fails validation"],
            ["  10 ", false, null, "float surrounded by spaces passes validation"],
            ["asdf", false, null, "string passes float validation"],
            ["13.23", true, 13.23, "string containing a floating-point-number fails float validation"],
            ["2e10", false, null, "number with exponential part passes float validation"],
            ["0b001010", false, null, "binary notation passes float validation"],
            ["0xFF", false, null, "hexadecimal notation passes float validation"],
            ["9223372036854775810", false, null, "too big float passes float validation"],
            ["-9223372036854775810", false, null, "too big negative float passes float validation"],
            ["-1000.2", true, -1000.2, "signed valid float fails validation"],
            ["-1000,2", true, -1000.2, "signed valid float fails validation"],
            ["+1000.4", true, 1000.4, "signed valid float fails validation"],
            ["0001234.6", true, 1234.6, "valid float fails validation"],
            ["0", true, 0, "valid float fails validation"],
            ["0.0", true, 0, "valid float fails validation"],
        ];
    }
    function provider_testRangeValidation()
    {
        return [
            [100, 200, true, '100', true, 100, "valid float fails validation"],
            [100, 200, true, '200', true, 200, "valid float fails validation"],
            [100, 200, true, '150', true, 150, "valid float fails validation"],
            [100, 200, true, '1500', false, null, "out-of-bounds float value passes validation"],
            [100, 200, false, '100.00', false, null, "out-of-bounds float (exclusive range) fails validation"],
            [100, 200, false, '200,00', false, null, "out-of-bounds float (exclusive range) fails validation"],
        ];
    }
    protected function createField(): Field
    {
        return Field::float("key");
    }
}