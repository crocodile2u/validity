<?php

namespace validity\test;

use \validity\FieldSet;
use \validity\Report;
use \validity\Field;

class FieldTest extends \PHPUnit_Framework_TestCase
{
    public function testRequiredFieldValidation()
    {
        $f = Field::any('key');

        $this->assertTrue($f->isValid(null));
        $this->assertEquals(null, $f->getFiltered());

        $f->setRequired();

        $this->assertFalse($f->isValid(null));
        $this->assertTrue($f->isValid('no array would be allowed here, must be explicitly set with expectArray()'));
    }

    public function testConditionalRequiredField()
    {
        $field = Field::any('key');
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
        $field = Field::any('key');

        $field->setDefault('default', true, false);
        $this->assertTrue($field->isValid(null), __METHOD__ . ": value should pass validation");
        $this->assertEquals('default', $field->getFiltered(), __METHOD__ . ": default value be used if value is missing");

        $this->assertTrue($field->isValid(''), __METHOD__ . ": value should pass validation");
        $this->assertEquals('default', $field->getFiltered(), __METHOD__ . ": default value should be used if value is empty");

        $field = Field::string('key')->setMinLength(10);
        $this->assertFalse($field->isValid('too short'));

        $field->setDefault('default', true, true);
        $Result = 'too short';
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": invalid value should be replaced with default");
        $this->assertEquals('default', $field->getFiltered(), __METHOD__ . ": default value should be used if value is incorrect");

        $field->setDefault('default', true, true);
        $Result = 'correct value';
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": value should pass validation");
        $this->assertEquals('correct value', $field->getFiltered(), __METHOD__ . ": correct value should not be replaced with anything else");

        $field = Field::enum('key', array('value 1', 'value 2'));
        $field->setDefault('default', true, true);
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": value should pass validation");
        $this->assertEquals('default', $field->getFiltered(), __METHOD__ . ": default value should be used if value is incorrect");

        $field = Field::enum('key', array('value 1', 'value 2'));
        $field->setDefault('default', true, false);
        $this->assertFalse($field->isValid('value 3'), __METHOD__ . ": value should not pass validation");
    }

    public function testArrayValidation()
    {
        $field = Field::any('key')->expectArray();
        $this->assertFalse($field->isValid('asdf'), __METHOD__ . ': value is expected to be an array but scalar passes validation');
        $this->assertTrue($field->isValid(['asdf']), __METHOD__ . ': value is expected to be an array but array fails validation');
        $this->assertTrue($field->isValid([]), __METHOD__ . ': value is expected to be an array but empty array fails validation');
    }

    public function testArrayValidationFailsIfAtLeastOneElementFailsValidation()
    {
        $field = Field::int('key')->expectArray();
        $this->assertFalse($field->isValid(array(-1, 0, 1, 'asdf')));
        $this->assertTrue($field->isValid(array(-1, 0, 1)));
    }

    public function testAssocValidation()
    {
        $NestedField = Field::any('nested_key');
        $Validator = (new FieldSet())->add(
            $NestedField
        );
        $field = Field::assoc('key', $Validator);
        $this->assertFalse($field->isValid('asdf'), __METHOD__ . ': value is expected to be an assoc but scalar passes validation');

        $field->isValid(array('asdf'));
        $this->assertTrue($field->isValid(array('asdf')), __METHOD__ . ': value is expected to be an assoc but array fails validation');
        $this->assertTrue($field->isValid(array()), __METHOD__ . ': value is expected to be an array but empty array fails validation');

        $NestedField->setRequired();
        $this->assertFalse($field->isValid([]), __METHOD__ . ': validation OK but nested values is missing');

        $this->assertTrue($field->isValid(['nested_key' => 'something']), __METHOD__ . ': validation fails for valid data');
    }

    public function testAssocValidationWithCallback()
    {
        $NestedField = Field::any('nested_key');
        $Validator = (new FieldSet())->add(
            $NestedField
        );

        $field = Field::assoc('key', $Validator)->addCallbackRule(
            function($value) {
                if ($value === ['nested_key' => 'valid']) {
                    return true;
                } else {
                    return false;
                }
            }
        );
        $this->assertTrue($field->isValid(['nested_key' => 'valid']), __METHOD__ . ': validation fails for valid data');

        $this->assertFalse($field->isValid(['nested_key' => 'invalid']), __METHOD__ . ': validation OK for INvalid data');
    }

    public function testIntegerFieldValidation()
    {
        $field = Field::int('key', 1);

        $int = '';
        $Result = $int;
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": optional empty string fails integer validation");

        $int = '10';
        $Result = $int;
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid integer fails validation");
        $this->assertEquals((int)$int, $field->getFiltered(), __METHOD__ . ": incorrect filtered value");

        $int = '  10  ';
        $Result = $int;
        $this->assertFalse($field->isValid($Result), __METHOD__ . ": integer surrounded by spaces passes validation");

        // incorrect format for integer
        $this->assertFalse($field->isValid('asdf'), __METHOD__ . ": string passes integer validation");
        $this->assertFalse(
            $field->isValid('15.36'),
            __METHOD__ . ": string containing a floating-point-number passes integer validation"
        );

        $this->assertFalse($field->isValid('0123e10'), __METHOD__ . ": number with exponential part passes integer validation");
        // PHP 5.4
        $this->assertFalse($field->isValid('0b001010'), __METHOD__ . ": binary notation passes integer validation");
        $this->assertFalse($field->isValid('0xFF'), __METHOD__ . ": hexadecimal notation  passes integer validation");

        $this->assertFalse($field->isValid('9223372036854775810'), __METHOD__ . ": too big integer passes integer validation");
        $this->assertFalse($field->isValid('-9223372036854775810'), __METHOD__ . ": too big integer passes integer validation");



        // check value falls within range
        $field = Field::int('key')->setMin(100)->setMax(200);

        $int = '100';
        $Result = $int;
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid integer fails validation");
        $this->assertEquals((int)$int, $field->getFiltered(), __METHOD__ . ": incorrect filtered value");

        $int = '200';
        $Result = $int;
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid integer fails validation");
        $this->assertEquals((int)$int, $field->getFiltered(), __METHOD__ . ": incorrect filtered value");

        $this->assertFalse($field->isValid('1500'), __METHOD__ . ": out-of-bounds integer value passes validation");

        $field = Field::int('key');
        $this->assertTrue($field->isValid('-1000'), __METHOD__ . ": signed valid integer fails validation");
        $this->assertTrue($field->isValid('+1000'), __METHOD__ . ": signed valid integer fails validation");
        $this->assertTrue($field->isValid('00002134'), __METHOD__ . ": valid integer fails validation");
        $this->assertTrue($field->isValid('0'), __METHOD__ . ": valid integer fails validation");

        $field = Field::int('key', 0)->setRequired();
        $this->assertTrue($field->isValid('0'), __METHOD__ . ": zero value fails integer validation");
    }

    public function testStringFieldValidation()
    {
        $field = Field::string('key');

        $Result = 'string';
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid string fails validation");
        $this->assertEquals('string', $field->getFiltered(), __METHOD__ . ": incorrect filtered value");

        $this->assertTrue($field->isValid('   string   '), __METHOD__ . ": valid string fails validation");
        $this->assertEquals('string', $field->getFiltered(), __METHOD__ . ": incorrect filtered value (must be trimmed)");

        $Result = '';
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid but empty string fails validation");
        $this->assertEquals('', $field->getFiltered(), __METHOD__ . ": incorrect filtered value (must be an empty string)");

        $Result = '     ';
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid but only containing spaces string fails validation");
        $this->assertEquals('', $field->getFiltered(), __METHOD__ . ": incorrect filtered value (must be trimmed to an empty string)");

        $Result = "\xF1";
        $this->assertFalse($field->isValid($Result), __METHOD__ . ": not valid utf8 string passed validation");
    }
    /** @dataProvider provider_testZeroValuePassesRequiredNumberValidation */
    public function testZeroValuePassesRequiredNumberValidation(Field $field, $expected_type, $expected_value)
    {
        $Result = '0';
        $this->assertTrue($field->isValid($Result));
        $value = $field->getFiltered();
        $this->assertEquals($expected_type, gettype($value), "DataField validation result returns value of a wrong type");
        $this->assertEquals($expected_value, $value, "DataField validation result returns incorrect value");
    }

    public function provider_testZeroValuePassesRequiredNumberValidation()
    {
        return array(
            array(Field::int('key')->setRequired(), 'integer', 0),
            array(Field::float('key')->setRequired(), 'double', 0.0),
        );
    }

    public function testBooleanFieldValidation()
    {
        $field = Field::bool('key');

        $valid = array(
            array(true, true),
            array(false, false),
            array(1, true),
            array(0, false),
            array('1', true),
            array('0', false),
            array('yes', true),
            array('no', false),
            array('on', true),
            array('off', false),
        );

        foreach ($valid as $spec) {
            list($input, $boolean) = $spec;
            $Result = $input;
            $this->assertTrue(
                $field->isValid($Result),
                __METHOD__ . ": " . var_export($input, 1) . " should be interpreted as a valid boolean"
            );
            $filtered = $field->getFiltered();
            $this->assertEquals(
                $boolean,
                $filtered,
                __METHOD__ . ": " . var_export($input, 1) . " should be interpreted as " . var_export($boolean, 1) . ", got " . var_export($filtered, 1)
            );
        }

        $Result = 'invalid';
        $this->assertFalse($field->isValid($Result));
    }

    public function testCallbackValidation()
    {
        $field = Field::string('key')->addCallbackRule(
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
        $field = Field::string('key')->addCallbackRule('non_existant_function');
        $field->isValid('anything');
    }

    public function testEnumFieldValidation()
    {
        $enum = array(1, false, 'asdf');
        $field = Field::enum('key', $enum);
        $this->assertEnumValidationSuccess($enum, $field);

        $field = Field::enum('key', [0, 1])->setRequired();
        $this->assertTrue($field->isValid('0'), __METHOD__ . ": zero value fails enum validation");
    }

    public function testMultipleRules()
    {
        $field = Field::int('key')->setMin(10)->setMax(20);

        // integer 10 - 20 is accepted
        $int = '10';
        $Result = $int;
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid value fails integer validation");
        $this->assertEquals((int)$int, $field->getFiltered(), __METHOD__ . ": incorrect filtered value");

        $int = '20';
        $Result = $int;
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid value fails integer validation");
        $this->assertEquals((int)$int, $field->getFiltered(), __METHOD__ . ": incorrect filtered value");

        $this->assertFalse($field->isValid('1500'), __METHOD__ . ": out-of-bounds integer value passes validation");

        // integer 15 - 20 is accepted
        $callback_threshold = 15;
        $field->addCallbackRule(
            function($value) use ($callback_threshold) {
                if ($value < $callback_threshold) {
                    return false;
                } else {
                    return true;
                }
            }
        );

        $int = $callback_threshold;
        $Result = $int;
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid value fails integer+callback validation");
        $this->assertEquals((int)$int, $field->getFiltered(), __METHOD__ . ": incorrect filtered value (integer+callback validation)");

        $int = '20';
        $Result = $int;
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid value fails integer+callback validation");
        $this->assertEquals((int)$int, $field->getFiltered(), __METHOD__ . ": incorrect filtered value (integer+callback validation)");

        $this->assertFalse($field->isValid('12'), __METHOD__ . ": out-of-bounds integer value passes integer+callback validation");
        $this->assertFalse($field->isValid('200'), __METHOD__ . ": out-of-bounds integer value passes integer+callback validation");
    }

    public function testDateTimeValidation()
    {
        $field = Field::datetime('key')->setMin('-1month')->setMax('+1month');

        $this->assertFalse($field->isValid('invalid date spec'), __METHOD__ . ": invalid date passes datetime validation");

        foreach (array('+1year', '-1year') as $invalid) {
            $this->assertFalse($field->isValid($invalid), __METHOD__ . ": invalid date (out of range) passes datetime validation");
        }

        $date = date('Y-m-d H:i:s');
        $Result = $date;
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid date fails datetime validation");
        $filtered = $field->getFiltered();
        $this->assertEquals($date, $filtered, __METHOD__ . ": filtered value holds an incorrect date/time");
    }

    public function testEmailValidation()
    {
        $field = Field::email('key');
        $this->assertFalse($field->isValid('invalid email'), __METHOD__ . ": invalid email passes validation");
        $this->assertTrue($field->isValid('valid@email.com'), __METHOD__ . ": valid email fails validation");
    }

    public function testPhoneValidation()
    {
        $field = Field::phone('key');
        $this->assertFalse($field->isValid('+7 888 (45 12345 3456 invalid phone'), __METHOD__ . ": invalid phone passes validation");
        $this->assertTrue($field->isValid('+7 (123 456-78-90'), __METHOD__ . ": valid phone fails validation");
    }

    public function testRegexpValidation()
    {
        $field = Field::string('key')->addRegexpRule('/^start \d+ finish$/xi');
        $this->assertFalse($field->isValid('start1234finish invalid'), __METHOD__ . ": invalid string passes regexp validation");
        $this->assertTrue($field->isValid('start1234finish'), __METHOD__ . ": valid string fails regexp validation");
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

        $field = Field::any('key')->addFilter($filter_1);
        $Result = 'val';
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid value fails validation (1)");
        $this->assertEquals($filter_1_ret, $field->getFiltered(), __METHOD__ . ": filtered value must contain what filter 1 returns");

        $field->addFilter($filter_2);
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": valid value fails validation (2)");
        $this->assertEquals($filter_2_ret, $field->getFiltered(), __METHOD__ . ": filtered value must contain what filter 2 returns");
    }

    private function assertEnumValidationSuccess($enum, Field $field)
    {
        foreach ($enum as $valid_value) {
            $this->assertTrue(
                $field->isValid($valid_value),
                __METHOD__ . ": enum validation does not accept valid value"
            );
        }

        $this->assertFalse(
            $field->isValid('invalid enum value'),
            __METHOD__ . ": enum validation must not accept invalid value"
        );
    }

    /**
     * @param $data
     * @return Report
     */
    private function result($data, $empty_if_null = true)
    {
        if (null === $data) {
            $data = $empty_if_null ? array() : array('key' => $data);
        } else {
            $data = array('key' => $data);
        }
        return new Report($data);
    }
}
