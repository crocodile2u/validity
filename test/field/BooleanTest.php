<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

class BooleanTest extends BaseFieldTest
{
    /**
     * @param mixed $value
     * @param bool $isValid
     * @param bool $expected
     * @dataProvider provider_testValidation
     */
    function testValidation($value, $isValid, $expected = null)
    {
        $field = Field::bool();
        $this->assertEquals(
            $isValid,
            $field->isValid($value),
            __METHOD__ . ": " . var_export($value, 1) . " should" . ($isValid ? "" : " NOT") . " be interpreted as a valid boolean"
        );
        if ($isValid) {
            $actual = $field->getFiltered();
            $this->assertEquals(
                $expected,
                $actual,
                __METHOD__ . ": " . var_export($value, 1) . " should be interpreted as " . var_export($expected, 1) . ", got " . var_export($actual, 1)
            );
        }
    }

    function provider_testValidation()
    {
        return [
            [true, true, true],
            [false, true, false],
            [1, true, true],
            [0, true, false],
            ['1', true, true],
            ['0', true, false],
            ['yes', true, true],
            ['no', true, false],
            ['on', true, true],
            ['off', true, false],
            ['invalid', false],
            ['', true, false],
        ];
    }
}