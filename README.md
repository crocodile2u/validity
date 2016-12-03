# Validity

> Validity is a simple validation and filtration package for PHP.

## Why?

The goal was simple: to have a reasonably small validation package that _easily_ covers all my needs:

* validating basic data types, without having to type a lot,
* validating arrays, validating against complex logic,
* validating values depending on other fields' values,
* easy filtering
* easy internationalization, understandable error messages for all cases &mdash; out-of-the-box, with ability to specify custom message for each and every case



## Features

* Basic field types: int, float, bool, string.
* More advanced types: date, datetime, email, phone, enum.
* Pattern validation.
* User-supplied callback validation.
* Validation of array values (f. e. array of checkboxes or text inputs).
* Validation of a compound field (field is an associative array).
* Filtration with user-supplied callbacks.
* Easy setting of limits (min/max) for numeric fields, date/datetime fields, setting min and max length for strings.
* Marking a field as required. You can also specify a condition under which a certain field is required (i. e., when a user selects "University" as education level, University name field can be set as required).
* Out-of-the-box English & Russian languages are at your disposal, containing default error messages for every case. You can easily create your own language class to use for default messages. At least for a boilerplate version of code those messages should work fine, without any need to write them by hand.
* Easy to override error messages for each and every validation rule: if you don't specify an error message, then a default one is selected from the language class. If a message is provided for a rule, that message is used.
* When dealing with strings, potentially malicious unicode characters are not allowed, for better security.

## Code example

```php

use \validity\FieldSet, \validity\Field;

$fieldSet = (new FieldSet())
        ->add(
            Field::pattern("name", "/^[A-Z][a-zA-Z\- ]+$/")
                ->setRequired()
        )->add(
            Field::enum("greeting", ["mr", "mrs"])->setRequired()
        )->add(
            Field::int("subscriptions")->setMin(1)->expectArray()
        )->add(
            Field::email("email")->setRequiredIf(
                function($value, \validity\Report $report) {
                    return (bool) $report->getFiltered("subscriptions");
                }
            )
        )->add(
            Field::date("date_of_birth")->setMax("-18years")->setRequired()
        )->add(
            Field::string("education")
                ->setMinLength(10)
                ->setMaxLength(100)
                ->expectArray()
                ->setArrayMinLength(0)
                ->setArrayMaxLength(3)
                ->setArraySkipEmpty(true)
        );

if ($fieldSet->isValid($_POST)) {
    $data = $fieldSet->getFiltered();
    // do something with $data
} else {
    // display errors summary
    echo $fieldSet->getErrors()->toString();
}
```

In this code example, no custom messages are used. Because the language is not specified for the _FieldSet_ contrustor, the default language class (English) will be used to provide pretty neat error messages. However, for every call that specifies a validation rule, you may supply a custom message, and it will then override the one from language pack. Messages can be provided in form of a template. _{label}_ is always replaced by the field's [label](#labels).
 
## Creating fields

Fields are created using named constructors:

* Field::**int**(string $name, string $message = null),
* Field::**float**(string $name, string $message = null),
* Field::**bool**(string $name, string $message = null),
* Field::**string**(string $name, string $message = null),
* Field::**date**(string $name, string $message = null),
* Field::**datetime**(string $name, string $message = null),
* Field::**enum**(string $name, array $values, string $message = null),
* Field::**email**(string $name, string $message = null),
* Field::**phone**(string $name, string $message = null),
* Field::**pattern**(string $name, string $pattern, string $message = null),
* Field::**assoc**(string $name, FieldSet $innerFieldSet, $message = null, $errorSeparator = "; ").

Assoc is a compound field, so _FieldSet_ will expect the value to be an array, which it will validate with a help of _$innerFieldSet_. A working example: set of three fields used to make up a date input: _date\[year\]_, _date\[month\]_, _date\[day\]_. In this case, _$innerFieldSet_ will contain rules for _year_, _month_ and _day_. $message parameter to Field::**assoc**() will only be used in case when _date_ key of the validated array (i. e. _$\_POST_) is not an array. If it is an array, then the _$innerFieldSet_ will take care of further validation. However, if the _$innerFieldSet_ reports any errors, they will all be combined into a string and used as an error message for the _date_ field by the outer _FieldSet_.

In all cases, _$message_ is used as error in case input cannot be interpreted as int, float etc.

## Simple constraints

Numeric fields (int &amp; float), as well as Date/Datetime fields can be assigned minimum and maximum values:

```php
Field::int("price")->setMin(1);
Field::date("date_of_birth")->setMax("-18years");
```

String values can be set to accept minumum and maximum string length:

```php
Field::string("name")
    ->setMinLength(2)
    ->setMaxLength(100);
```

In case an array is expected, it can also be limited:

```php
Field::string("education")
    ->setMaxLength(100)
    ->expectArray()
    ->setArrayMinLength(0)
    ->setArrayMaxLength(3)
```

In the last case, _setMaxLength(100)_ limits the length of every string in the _education_ array, while _setArrayMinLength()_ and _setArrayMaxLength()_ set the limits for the array size.

## Labels

Every field must have a name. Name is the first and required parameter to all the [named constructors](#creating-fields). Name is essentially the key of the associative array the _FieldSet_ will validate. In addition, field can also have a label. For example, field name is _date_of_birth_ but label is _Date of birth_. Label can be set with _Field->setLabel(string $label)_. If not set, field name is used as label.