<?php

namespace validity\test;

use validity\FieldSet;
use validity\Report;
use validity\Field;

class FieldSetTest extends \PHPUnit_Framework_TestCase
{
    public function testCheck()
    {
        $V = new FieldSet();
        $V->add(new StubFieldPass())
            ->add(new StubFieldFail());

        $data = array(
            StubFieldPass::NAME => StubFieldPass::NAME . ' data',
            StubFieldFail::NAME => 'anything',
        );

        $this->assertFalse($V->isValid($data));
        $this->assertEquals($data[StubFieldPass::NAME], $V->getFiltered(StubFieldPass::NAME));
        $this->assertEquals(null, $V->getFiltered(StubFieldFail::NAME));
        $this->assertEquals(1, count($V->getFiltered()));

        $errors = $V->getErrors();
        $this->assertEquals(1, count($errors));
        $this->assertTrue(array_key_exists(StubFieldFail::NAME, $errors));
        $this->assertEquals(StubFieldFail::ERROR_MESSAGE, $errors->getAsString(StubFieldFail::NAME));

        $V = new FieldSet();
        $V->add(
            Field::enum('type', array('value 1', 'value 2'))->setDefault('default', true, true)
        );
        $input = array('type' => false);
        $this->assertTrue($V->isValid($input), __METHOD__ . ": value should pass validation");
    }
}

class StubFieldPass extends Field
{
    const NAME = 'pass';
    public function __construct()
    {
        parent::__construct(self::NAME, null);
    }
    public function isValid($value): bool
    {
        $this->currentValue = $value;
        $this->updateFilteredValue();
        return true;
    }
}

class StubFieldFail extends Field
{
    const NAME = 'fail';
    const ERROR_MESSAGE = 'failed';
    public function __construct()
    {
        parent::__construct(self::NAME, null);
    }
    public function isValid($value): bool
    {
        $this->addError(self::ERROR_MESSAGE);
        return false;
    }
}
