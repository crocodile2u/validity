<?php

namespace validity\test;

use \validity\FieldSet;
use \validity\Report;
use \validity\Field;

class FieldTest extends \PHPUnit_Framework_TestCase
{
    public function testRequiredFieldValidation()
    {
        $F = Field::any('key');

        $Result = $this->result(null);
        $this->assertTrue($F->isValid($Result));
        $this->assertEquals(null, $Result->getFiltered('key'));

        $F->setRequired();

        $this->assertFalse($F->isValid($this->result(null)));
        $this->assertTrue($F->isValid($this->result('no array would be allowed here, must be explicitly set with expectArray()')));
    }

    public function testConditionalRequiredField()
    {
        $F = Field::any('key');
        $F->setRequiredIf(false);
        $this->assertTrue($F->isValid($this->result(null)), __METHOD__ . ": Conditional required field should pass validation");

        $F->setRequiredIf(true);
        $this->assertFalse($F->isValid($this->result(null)), __METHOD__ . ": Conditional required field should fail validation");

        // value is required
        $F->setRequiredIf(
            function() {
                return true;
            }
        );
        $this->assertFalse($F->isValid($this->result(null)), __METHOD__ . ": Conditional required field should fail validation");

        // value is actually NOT required as the callback returns FALSE
        $F->setRequiredIf(
            function() {
                return false;
            }
        );
        $this->assertTrue($F->isValid($this->result(null)), __METHOD__ . ": Conditional required field should pass validation");
    }

    public function testDefaultValues()
    {
        $field = Field::any('key');

        $field->setDefault('default', true, false);
        $Result = $this->result(null);
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": value should pass validation");
        $this->assertEquals('default', $Result->getFiltered('key'), __METHOD__ . ": default value be used if value is missing");

        $Result = $this->result('   ');
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": value should pass validation");
        $this->assertEquals('default', $Result->getFiltered('key'), __METHOD__ . ": default value should be used if value is empty");

        $field = Field::string('key')->setMinLength(10);
        $this->assertFalse($field->isValid($this->result('too short')));

        $field->setDefault('default', true, true);
        $Result = $this->result('too short');
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": invalid value should be replaced with default");
        $this->assertEquals('default', $Result->getFiltered('key'), __METHOD__ . ": default value should be used if value is incorrect");

        $field->setDefault('default', true, true);
        $Result = $this->result('correct value');
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": value should pass validation");
        $this->assertEquals('correct value', $Result->getFiltered('key'), __METHOD__ . ": correct value should not be replaced with anything else");

        $field = Field::enum('key', array('value 1', 'value 2'));
        $field->setDefault('default', true, true);
        $Result = $this->result(false);
        $this->assertTrue($field->isValid($Result), __METHOD__ . ": value should pass validation");
        $this->assertEquals('default', $Result->getFiltered('key'), __METHOD__ . ": default value should be used if value is incorrect");
        $this->assertTrue($Result->isOk(), "Validation resull should be OK");

        $field = Field::enum('key', array('value 1', 'value 2'));
        $field->setDefault('default', true, false);
        $Result = $this->result('value 3');
        $this->assertFalse($field->isValid($Result), __METHOD__ . ": value should not pass validation");
        $this->assertTrue(array_key_exists("key", $Result->getErrors()));
    }

    public function testArrayValidation()
    {
        $F = Field::any('key')->expectArray();
        $this->assertFalse($F->isValid($this->result('asdf')), __METHOD__ . ': value is expected to be an array but scalar passes validation');
        $this->assertTrue($F->isValid($this->result(array('asdf'))), __METHOD__ . ': value is expected to be an array but array fails validation');
        $this->assertTrue($F->isValid($this->result(array())), __METHOD__ . ': value is expected to be an array but empty array fails validation');
    }

    public function testArrayValidationFailsIfAtLeastOneElementFailsValidation()
    {
        $F = Field::int('key')->expectArray();
        $this->assertFalse($F->isValid($this->result(array(-1, 0, 1, 'asdf'))));
        $this->assertTrue($F->isValid($this->result(array(-1, 0, 1))));
    }

    public function testAssocValidation()
    {
        $NestedField = Field::any('nested_key');
        $Validator = (new FieldSet())->add(
            $NestedField
        );
        $F = Field::assoc('key', $Validator);
        $this->assertFalse($F->isValid($this->result('asdf')), __METHOD__ . ': value is expected to be an assoc but scalar passes validation');

        $F->isValid($this->result(array('asdf')));
        $this->assertTrue($F->isValid($this->result(array('asdf'))), __METHOD__ . ': value is expected to be an assoc but array fails validation');
        $this->assertTrue($F->isValid($this->result(array())), __METHOD__ . ': value is expected to be an array but empty array fails validation');

        $NestedField->setRequired();
        $Result = $this->result([]);
        $this->assertFalse($F->isValid($Result), __METHOD__ . ': validation OK but nested values is missing');
        $this->assertTrue(array_key_exists('key', $Result->getErrors()));

        $this->assertTrue($F->isValid($this->result(['nested_key' => 'something'])), __METHOD__ . ': validation fails for valid data');
    }

    public function testAssocValidationWithCallback()
    {
        $NestedField = Field::any('nested_key');
        $Validator = (new FieldSet())->add(
            $NestedField
        );

        $F = Field::assoc('key', $Validator)->addCallbackRule(
            function($name, $value, $message, Report $Result) {
                if ($value === ['nested_key' => 'valid']) {
                    return $value;
                } else {
                    $Result->addError($name, $message);
                    return null;
                }
            }
        );
        $this->assertTrue($F->isValid($this->result(['nested_key' => 'valid'])), __METHOD__ . ': validation fails for valid data');

        $Result = $this->result(['nested_key' => 'invalid']);
        $this->assertFalse($F->isValid($Result), __METHOD__ . ': validation OK for INvalid data');
        $this->assertTrue(array_key_exists('key', $Result->getErrors()));
    }

    public function testIntegerFieldValidation()
    {
        $F = Field::int('key', 1);

        $int = '';
        $Result = $this->result($int);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": optional empty string fails integer validation");

        $int = '10';
        $Result = $this->result($int);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid integer fails validation");
        $this->assertEquals((int)$int, $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value");

        $int = '  10  ';
        $Result = $this->result($int);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid integer surrounded by spaces fails validation");
        $this->assertEquals((int)$int, $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value");

        // incorrect format for integer
        $this->assertFalse($F->isValid($this->result('asdf')), __METHOD__ . ": string passes integer validation");
        $this->assertFalse(
            $F->isValid($this->result('15.36')),
            __METHOD__ . ": string containing a floating-point-number passes integer validation"
        );

        $this->assertFalse($F->isValid($this->result('0123e10')), __METHOD__ . ": number with exponential part passes integer validation");
        // PHP 5.4
        $this->assertFalse($F->isValid($this->result('0b001010')), __METHOD__ . ": binary notation passes integer validation");
        $this->assertFalse($F->isValid($this->result('0xFF')), __METHOD__ . ": hexadecimal notation  passes integer validation");

        $this->assertFalse($F->isValid($this->result('9223372036854775810')), __METHOD__ . ": too big integer passes integer validation");
        $this->assertFalse($F->isValid($this->result('-9223372036854775810')), __METHOD__ . ": too big integer passes integer validation");



        // check value falls within range
        $F = Field::int('key')->setMin(100)->setMax(200);

        $int = '100';
        $Result = $this->result($int);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid integer fails validation");
        $this->assertEquals((int)$int, $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value");

        $int = '200';
        $Result = $this->result($int);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid integer fails validation");
        $this->assertEquals((int)$int, $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value");

        $this->assertFalse($F->isValid($this->result('1500')), __METHOD__ . ": out-of-bounds integer value passes validation");

        $F = Field::int('key');
        $this->assertTrue($F->isValid($this->result('-1000')), __METHOD__ . ": signed valid integer fails validation");
        $this->assertTrue($F->isValid($this->result('+1000')), __METHOD__ . ": signed valid integer fails validation");
        $this->assertTrue($F->isValid($this->result('00002134')), __METHOD__ . ": valid integer fails validation");
        $this->assertTrue($F->isValid($this->result('0')), __METHOD__ . ": valid integer fails validation");

        $F = Field::int('key', 0)->setRequired();
        $this->assertTrue($F->isValid($this->result('0')), __METHOD__ . ": zero value fails integer validation");
    }

    public function testStringFieldValidation()
    {
        $F = Field::string('key');

        $Result = $this->result('string');
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid string fails validation");
        $this->assertEquals('string', $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value");

        $Result = $this->result('   string   ');
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid string fails validation");
        $this->assertEquals('string', $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value (must be trimmed)");

        $Result = $this->result('');
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid but empty string fails validation");
        $this->assertEquals('', $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value (must be an empty string)");

        $Result = $this->result('     ');
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid but only containing spaces string fails validation");
        $this->assertEquals('', $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value (must be trimmed to an empty string)");

        $Result = $this->result("\xF1");
        $this->assertFalse($F->isValid($Result), __METHOD__ . ": not valid utf8 string passed validation");
    }
    /** @dataProvider provider_testZeroValuePassesRequiredNumberValidation */
    public function testZeroValuePassesRequiredNumberValidation(Field $F, $expected_type, $expected_value)
    {
        $Result = $this->result('0');
        $this->assertTrue($F->isValid($Result));
        $value = $Result->getFiltered('key');
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
        $F = Field::bool('key');

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
            $Result = $this->result($input);
            $this->assertTrue(
                $F->isValid($Result),
                __METHOD__ . ": " . var_export($input, 1) . " should be interpreted as a valid boolean"
            );
            $filtered = $Result->getFiltered('key');
            $this->assertEquals(
                $boolean,
                $filtered,
                __METHOD__ . ": " . var_export($input, 1) . " should be interpreted as " . var_export($boolean, 1) . ", got " . var_export($filtered, 1)
            );
        }

        $Result = $this->result('invalid');
        $this->assertFalse($F->isValid($Result));
    }

    public function testCallbackValidation()
    {
        $F = Field::callback(
            'key',
            function($name, $value, $message, Report $Result) {
                if ($value === 'valid') {
                    return $value;
                } else {
                    $Result->addError($name, $message);
                }
            }
        );

        $this->assertTrue(
            $F->isValid($this->result('valid')),
            __METHOD__ . ": callback validation does not accept valid value"
        );

        $this->assertFalse(
            $F->isValid($this->result('invalid')),
            __METHOD__ . ": callback validation must not accept invalid value"
        );

        $this->expectException(\TypeError::class);
        $F = Field::callback('key', 'non_existant_function');
        $F->isValid($this->result('anything'));
    }

    public function testEnumFieldValidation()
    {
        $enum = array(1, false, 'asdf');
        $F = Field::enum('key', $enum);
        $this->assertEnumValidationSuccess($enum, $F);

        $F = Field::enum('key', [0, 1])->setRequired();
        $this->assertTrue($F->isValid($this->result('0')), __METHOD__ . ": zero value fails enum validation");
    }

    public function testMultipleRules()
    {
        $F = Field::int('key')->setMin(10)->setMax(20);

        // integer 10 - 20 is accepted
        $int = '10';
        $Result = $this->result($int);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid value fails integer validation");
        $this->assertEquals((int)$int, $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value");

        $int = '20';
        $Result = $this->result($int);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid value fails integer validation");
        $this->assertEquals((int)$int, $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value");

        $this->assertFalse($F->isValid($this->result('1500')), __METHOD__ . ": out-of-bounds integer value passes validation");

        // integer 15 - 20 is accepted
        $callback_threshold = 15;
        $F->addCallbackRule(
            function($name, $value, $message, Report $Result) use ($callback_threshold) {
                if ($value < $callback_threshold) {
                    $Result->addError($name, $message);
                    return null;
                } else {
                    return $value;
                }
            }
        );

        $int = $callback_threshold;
        $Result = $this->result($int);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid value fails integer+callback validation");
        $this->assertEquals((int)$int, $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value (integer+callback validation)");

        $int = '20';
        $Result = $this->result($int);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid value fails integer+callback validation");
        $this->assertEquals((int)$int, $Result->getFiltered('key'), __METHOD__ . ": incorrect filtered value (integer+callback validation)");

        $this->assertFalse($F->isValid($this->result('12')), __METHOD__ . ": out-of-bounds integer value passes integer+callback validation");
        $this->assertFalse($F->isValid($this->result('200')), __METHOD__ . ": out-of-bounds integer value passes integer+callback validation");
    }

    public function testDateTimeValidation()
    {
        $F = Field::datetime('key')->setMin('-1month')->setMax('+1month');

        $this->assertFalse($F->isValid($this->result('invalid date spec')), __METHOD__ . ": invalid date passes datetime validation");

        foreach (array('+1year', '-1year') as $invalid) {
            $this->assertFalse($F->isValid($this->result($invalid)), __METHOD__ . ": invalid date (out of range) passes datetime validation");
        }

        $date = date('Y-m-d H:i:s');
        $Result = $this->result($date);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid date fails datetime validation");
        $filtered = $Result->getFiltered('key');
        $this->assertEquals($date, $filtered, __METHOD__ . ": filtered value holds an incorrect date/time");
    }

    public function testEmailValidation()
    {
        $F = Field::email('key');
        $this->assertFalse($F->isValid($this->result('invalid email')), __METHOD__ . ": invalid email passes validation");
        $this->assertTrue($F->isValid($this->result('valid@email.com')), __METHOD__ . ": valid email fails validation");
    }

    public function testPhoneValidation()
    {
        $F = Field::phone('key');
        $this->assertFalse($F->isValid($this->result('+7 888 (45) 12345 3456 invalid phone')), __METHOD__ . ": invalid phone passes validation");
        $this->assertTrue($F->isValid($this->result('+7 (123) 456-78-90')), __METHOD__ . ": valid phone fails validation");
    }

    public function testRegexpValidation()
    {
        $F = Field::string('key')->addRegexpRule('/^start \d+ finish$/xi');
        $this->assertFalse($F->isValid($this->result('start1234finish invalid')), __METHOD__ . ": invalid string passes regexp validation");
        $this->assertTrue($F->isValid($this->result('start1234finish')), __METHOD__ . ": valid string fails regexp validation");
    }

    public function testFilters()
    {
        $filter_1_ret = 'filter 1';
        $filter_1 = function($value) use ($filter_1_ret) {
            return $filter_1_ret;
        };

        $filter_2_ret = 'filter 2';
        $filter_2 = function($value) use ($filter_2_ret) {
            return $filter_2_ret;
        };

        $F = Field::any('key')->addFilter($filter_1);
        $Result = $this->result('val');
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid value fails validation (1)");
        $this->assertEquals($filter_1_ret, $Result->getFiltered('key'), __METHOD__ . ": filtered value must contain what filter 1 returns");

        $F->addFilter($filter_2);
        $this->assertTrue($F->isValid($Result), __METHOD__ . ": valid value fails validation (2)");
        $this->assertEquals($filter_2_ret, $Result->getFiltered('key'), __METHOD__ . ": filtered value must contain what filter 2 returns");
    }

    private function assertEnumValidationSuccess($enum, Field $F)
    {
        foreach ($enum as $valid_value) {
            $this->assertTrue(
                $F->isValid($this->result($valid_value)),
                __METHOD__ . ": enum validation does not accept valid value"
            );
        }

        $this->assertFalse(
            $F->isValid($this->result('invalid enum value')),
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
