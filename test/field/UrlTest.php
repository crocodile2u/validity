<?php

namespace validity\test\field;

use validity\Field;
use validity\test\BaseFieldTest;

class UrlTest extends BaseFieldTest
{
    /**
     * @param string $input
     * @param bool $isValid
     * @param string $expected
     * @dataProvider provider_testValidation
     */
    function testValidation($input, $isValid, $expected = null)
    {
        $field = Field::url("key");
        $this->assertEquals($isValid, $field->isValid($input));
        if ($isValid) {
            $this->assertEquals($expected, $field->getFiltered());
        }
    }

    function provider_testValidation()
    {
        return [
            ["http://ya.ru/", true, "http://ya.ru/"],
            ["http://ya.ru", true, "http://ya.ru"],
            ["http://ya.ru?q=test", true, "http://ya.ru?q=test"],
            ["http://ya.ru/?q=test#fragment", true, "http://ya.ru/?q=test#fragment"],
            ["invalid URL", false],
            ["://missing-scheme.com/", false],
            ["missing-domain://", false],
        ];
    }
}