<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

class StrTest extends BaseFieldTest
{
    function testBasicRules()
    {
        $field = Field::string();

        $this->assertTrue($field->isValid('string'), __METHOD__ . ": valid string fails validation");
        $this->assertEquals('string', $field->getFiltered(), __METHOD__ . ": incorrect filtered value");

        $this->assertTrue($field->isValid('   string   '), __METHOD__ . ": valid string fails validation");
        $this->assertEquals('string', $field->getFiltered(), __METHOD__ . ": incorrect filtered value (must be trimmed)");

        $this->assertTrue($field->isValid(''), __METHOD__ . ": valid but empty string fails validation");
        $this->assertEquals('', $field->getFiltered(), __METHOD__ . ": incorrect filtered value (must be an empty string)");

        $this->assertTrue($field->isValid('   '), __METHOD__ . ": valid whitespace-only string fails validation");
        $this->assertEquals('', $field->getFiltered(), __METHOD__ . ": incorrect filtered value (must be trimmed to an empty string)");

        $this->assertFalse($field->isValid("\xF1"), __METHOD__ . ": not valid utf8 string passed validation");
    }

    function testLengthValidation()
    {
        $field = Field::string()->setMinLength(2)->setMaxLength(5);

        $this->assertTrue($field->isValid('22'));
        $this->assertTrue($field->isValid('333'));
        $this->assertTrue($field->isValid('55555'));

        $this->assertFalse($field->isValid('1'));
        $this->assertFalse($field->isValid('666666'));
    }

    public function testRegexpValidation()
    {
        $field = Field::string()->addRegexpRule('/^start \d+ finish$/xi');
        $this->assertFalse($field->isValid('start1234finish invalid'), __METHOD__ . ": invalid string passes regexp validation");
        $this->assertTrue($field->isValid('start1234finish'), __METHOD__ . ": valid string fails regexp validation");
    }
}