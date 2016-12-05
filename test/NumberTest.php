<?php

namespace validity\test;

use validity\Field;

require_once __DIR__ . "/BaseFieldTest.php";

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
            $this->assertValid(["key" => $input], $field, $filtered, $message);
        } else {
            $this->assertInvalid(["key" => $input], $field, $message);
        }
    }
    abstract function provider_testIsValid();

    /**
     * @param int|float $min
     * @param int|float $max
     * @param mixed $value
     * @param bool $isValid
     * @param int $filtered
     * @param string $message
     * @dataProvider provider_testRangeValidation
     */
    function testRangeValidation($min, $max, $value, $isValid, $filtered, $message)
    {
        $field = $this->createField()->setMin($min)->setMax($max);
        if ($isValid) {
            $this->assertValid(["key" => $value], $field, $filtered, $message);
        } else {
            $this->assertInvalid(["key" => $value], $field, $message);
        }
    }
    abstract function provider_testRangeValidation();

    function testZeroIsConsideredValidEvenForRequiredField()
    {
        $field = $this->createField()->setRequired();
        $this->assertValid(["key" => 0], $field, 0, "zero value fails integer validation (required)");
    }

    abstract protected function createField(): Field;
}