<?php

namespace validity\test;

use PHPUnit\Framework\TestCase;
use \validity\Field;

class FieldTest extends TestCase
{
    public function testRequiredFieldValidation()
    {
        $f = Field::any();

        $this->assertTrue($f->isValid(null));
        $this->assertEquals(null, $f->getFiltered());

        $f->setRequired();

        $this->assertFalse($f->isValid(null));
        $this->assertTrue($f->isValid('no array would be allowed here, must be explicitly set with expectArray()'));
    }

    public function testConditionalRequiredField()
    {
        $field = Field::any();
        $field->setRequiredIf(false);
        $this->assertTrue($field->isValid(null), __METHOD__ . ": Conditional required field should pass validation");

        $field->setRequiredIf(true);
        $this->assertFalse($field->isValid(null), __METHOD__ . ": Conditional required field should fail validation");

        // value is required
        $field->setRequiredIf(
            function() {
                return true;
            }
        );
        $this->assertFalse($field->isValid(null), __METHOD__ . ": Conditional required field should fail validation");

        // value is actually NOT required as the callback returns FALSE
        $field->setRequiredIf(
            function() {
                return false;
            }
        );
        $this->assertTrue($field->isValid(null), __METHOD__ . ": Conditional required field should pass validation");
    }

    public function testDefaultValues()
    {
        $field = Field::any();

        $field->setDefault('default', true, false);
        $this->assertTrue($field->isValid(null), __METHOD__ . ": value should pass validation");
        $this->assertEquals('default', $field->getFiltered(), __METHOD__ . ": default value be used if value is missing");

        $this->assertTrue($field->isValid(''), __METHOD__ . ": value should pass validation");
        $this->assertEquals('default', $field->getFiltered(), __METHOD__ . ": default value should be used if value is empty");

        $field = Field::string()->setMinLength(10);
        $this->assertFalse($field->isValid('too short'));

        $field->setDefault('default', true, true);
        $this->assertTrue($field->isValid('too short'), __METHOD__ . ": invalid value should be replaced with default");
        $this->assertEquals('default', $field->getFiltered(), __METHOD__ . ": default value should be used if value is incorrect");

        $field->setDefault('default', true, true);
        $this->assertTrue($field->isValid('correct value'), __METHOD__ . ": value should pass validation");
        $this->assertEquals('correct value', $field->getFiltered(), __METHOD__ . ": correct value should not be replaced with anything else");

        $field = Field::enum('key', array('value 1', 'value 2'));
        $field->setDefault('default', true, true);
        $this->assertTrue($field->isValid('correct value'), __METHOD__ . ": value should pass validation");
        $this->assertEquals('default', $field->getFiltered(), __METHOD__ . ": default value should be used if value is incorrect");

        $field = Field::enum('key', array('value 1', 'value 2'));
        $field->setDefault('default', true, false);
        $this->assertFalse($field->isValid('value 3'), __METHOD__ . ": value should not pass validation");
    }

    public function testArrayValidation()
    {
        $field = Field::any()->expectArray();
        $this->assertFalse($field->isValid('asdf'), __METHOD__ . ': value is expected to be an array but scalar passes validation');
        $this->assertTrue($field->isValid(['asdf']), __METHOD__ . ': value is expected to be an array but array fails validation');
        $this->assertTrue($field->isValid([]), __METHOD__ . ': value is expected to be an array but empty array fails validation');
    }

    public function testArrayValidationFailsIfAtLeastOneElementFailsValidation()
    {
        $field = Field::int()->expectArray();
        $this->assertFalse($field->isValid(array(-1, 0, 1, 'asdf')));
        $this->assertTrue($field->isValid(array(-1, 0, 1)));
    }

    public function testCallbackValidation()
    {
        $field = Field::any()->addCallbackRule(
            function($value) {
                if ($value === 'valid') {
                    return $value;
                } else {
                    return false;
                }
            }
        );

        $this->assertTrue(
            $field->isValid('valid'),
            __METHOD__ . ": callback validation does not accept valid value"
        );

        $this->assertFalse(
            $field->isValid('invalid'),
            __METHOD__ . ": callback validation must not accept invalid value"
        );

        $this->expectException(\TypeError::class);
        $field = Field::string()->addCallbackRule('non_existant_function');
        $field->isValid('anything');
    }

    public function testMultipleRules()
    {
        $field = Field::int()->setMin(10)->setMax(20);

        // integer 10 - 20 is accepted
        $this->assertTrue($field->isValid('10'), __METHOD__ . ": valid value fails integer validation");
        $this->assertEquals(10, $field->getFiltered(), __METHOD__ . ": incorrect filtered value");

        $this->assertTrue($field->isValid('20'), __METHOD__ . ": valid value fails integer validation");
        $this->assertEquals(20, $field->getFiltered(), __METHOD__ . ": incorrect filtered value");

        $this->assertFalse($field->isValid('1500'), __METHOD__ . ": out-of-bounds integer value passes validation");

        // integer 15 - 20 is accepted
        $callbackThreshold = 15;
        $field->addCallbackRule(
            function($value) use ($callbackThreshold) {
                if ($value < $callbackThreshold) {
                    return false;
                } else {
                    return true;
                }
            }
        );

        $this->assertTrue($field->isValid($callbackThreshold), __METHOD__ . ": valid value fails integer+callback validation");
        $this->assertEquals($callbackThreshold, $field->getFiltered(), __METHOD__ . ": incorrect filtered value (integer+callback validation)");

        $this->assertTrue($field->isValid('20'), __METHOD__ . ": valid value fails integer+callback validation");
        $this->assertEquals(20, $field->getFiltered(), __METHOD__ . ": incorrect filtered value (integer+callback validation)");

        $this->assertFalse($field->isValid('12'), __METHOD__ . ": out-of-bounds integer value passes integer+callback validation");
        $this->assertFalse($field->isValid('200'), __METHOD__ . ": out-of-bounds integer value passes integer+callback validation");
    }

    public function testFilters()
    {
        $filter_1_ret = 'filter 1';
        $filter_1 = function() use ($filter_1_ret) {
            return $filter_1_ret;
        };

        $filter_2_ret = 'filter 2';
        $filter_2 = function() use ($filter_2_ret) {
            return $filter_2_ret;
        };

        $field = Field::any()->addFilter($filter_1);
        $Result = 'val';
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid value fails validation (1)");
        $this->assertEquals($filter_1_ret, $field->getFiltered(), __METHOD__ . ": filtered value must contain what filter 1 returns");

        $field->addFilter($filter_2);
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid value fails validation (2)");
        $this->assertEquals($filter_2_ret, $field->getFiltered(), __METHOD__ . ": filtered value must contain what filter 2 returns");
    }
}
