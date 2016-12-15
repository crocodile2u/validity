<?php

namespace validity\test;

use validity\Field;

abstract class NumberTest extends BaseFieldTest
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
        $field = $this->createField();
        if ($isValid) {
            $this->assertValid($input, $field, $filtered, $message);
        } else {
            $this->assertInvalid($input, $field, null, $message);
        }
    }
    abstract function provider_testIsValid();

    /**
     * @param int|float $min
     * @param int|float $max
     * @param bool $inclusive
     * @param mixed $value
     * @param bool $isValid
     * @param int $filtered
     * @param string $message
     * @dataProvider provider_testRangeValidation
     */
    function testRangeValidation($min, $max, $inclusive, $value, $isValid, $filtered, $message)
    {
        $field = $this->createField()->setMin($min, $inclusive)->setMax($max, $inclusive);
        if ($isValid) {
            $this->assertValid($value, $field, $filtered, $message);
        } else {
            $this->assertInvalid($value, $field, null, $message);
        }
    }
    abstract function provider_testRangeValidation();

    function testZeroIsConsideredValidEvenForRequiredField()
    {
        $field = $this->createField()->setRequired();
        $this->assertValid(0, $field, 0, "zero value fails integer validation (required)");
    }

    abstract protected function createField(): Field;
}