<?php

namespace validity\test\field;

use validity\Field;
use validity\FieldSet;
use validity\test\BaseFieldTest;

class AssocTest extends BaseFieldTest
{
    public function testBasicValidation()
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
}