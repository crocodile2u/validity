# Validity

> Validity is a simple validation and filtration package for PHP. It can be used to validate both single values like integers and strings, and complex nested structures, using however complex validation logic you can think of. Nice default errors messages in English or Russian. Easily extensible with a custom language package, with always having ability to provide a custom error message for any rule on every field.

## Why?

The goal was simple: to have a reasonably small validation package that _easily_ covers all my needs:

* validating basic data types, without having to type a lot,
* validating arrays, validating against complex logic,
* validating values depending on other fields' values,
* filtering of value
* internationalization, nice error messages for all cases &mdash; out-of-the-box
* ability to specify custom message for each and every case

## Features

* Basic field types: int, float, bool, string.
* More advanced types: date, datetime, email, phone, enum.
* Pattern validation.
* User-supplied callback validation.
* Validation of array values (f. e. array of checkboxes or text inputs). Precise error messages (ability to display the exact key on which the error happened). Messages are internally represented as objects containing array key information, if relevant. Every message is exported to JSON as {text: &lt;string&gt;, "key": &lt;null or string or int&gt;}
* Validation of a compound field (field is an associative array).
* Filtration with user-supplied callbacks.
* Easy setting of limits (min/max) for numeric fields, date/datetime fields, setting min and max length for strings.
* Marking a field as required. You can also specify a condition under which a certain field is required (i. e., when a user selects "University" as education level, University name field can be set as required).
* Out-of-the-box English & Russian languages are at your disposal, containing default error messages for every case. You can easily create your own language class to use for default messages. At least for a boilerplate version of code those messages should work fine, without having to write a single message by hand.
* Easy to override error messages for each and every validation rule: if you don't specify an error message, then a default one is selected from the language class. If a message is provided for a rule, that message is used.
* When dealing with strings, potentially malicious unicode characters are not allowed, for better security.

## Examples

A few examples can be found in the _examples_ folder. The easiest way to see them is to let your web-server serve these scripts: I simply symlink the examples folder below my localhost's _$root_.

I will also provide an example right here:

```php

use \validity\FieldSet, \validity\Field;

$name = Field::pattern("name", "/^[A-Z][a-zA-Z\- ]+$/")->setRequired();
$greeting = Field::enum("greeting", ["mr", "mrs"])->setRequired();
$subscriptions = Field::int("subscriptions")->setMin(1)->expectArray();
$email = Field::email("email")->setRequiredIf(
    function(FieldSet $fieldSet) {
        return (bool) $fieldSet->getFiltered("subscriptions");
    }
);
$dateOfBirth = Field::date("date_of_birth")->setMax("-18years")->setRequired();
$education = Field::string("education")
     ->setMinLength(10)
     ->setMaxLength(100)
     ->expectArray()
     ->setArrayMinLength(0)
     ->setArrayMaxLength(3)
     ->setArraySkipEmpty(true);

$fieldSet = (new FieldSet())
        ->add($name)
        ->add($greeting)
        ->add($subscriptions)
        ->add($email)
        ->add($dateOfBirth)
        ->add($education);

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

* Field::**int**(string $name = null, string $message = null),
* Field::**float**(string $name = null, string $message = null),
* Field::**bool**(string $name = null, string $message = null),
* Field::**string**(string $name = null, string $message = null),
* Field::**date**(string $name = null, string $message = null),
* Field::**datetime**(string $name = null, string $message = null),
* Field::**enum**(string $name = null, array $values, string $message = null),
* Field::**email**(string $name = null, string $message = null),
* Field::**phone**(string $name = null, string $message = null),
* Field::**pattern**(string $name = null, string $pattern, string $message = null),
* Field::**assoc**(string $name = null, FieldSet $innerFieldSet, $message = null, $errorSeparator = "; ").

Every named constructor return a _Field_ of a corresponding type, most of them being pretty much self-explanating, with an exception of **Assoc**, which you can know more about in [Validating compound values](#validating-compound-values) section.

In all cases, _$message_ is used as error in case input cannot be interpreted as int, float etc.

## Required fields

Field can be marked as required:

```php
Field::string("username")->setRequired("Please enter username");
```

Sometimes, a field is only required in case some other fields are filled (or any other conditions are met).

```php
Field::email("email")->setRequiredIf(
    function(FieldSet $fieldSet) {
        return (bool) $fieldSet->getFiltered("subscriptions");
    }
);
```

_setRequiredIf()_ accepts either a boolean or a callback as argument. In first case, it simply marks the field as required or removes this mark. When a callable is used, it is evaluated in the process of validation, and the field is only considered as required if the callback returns TRUE. In the example above, the _email_ field is required in case the user has chosen to subscribe to some mailing list.

## Default values

Every field can be assigned a default value:

```php
Field::int("price")->setDefault(100, true, false);
```

The two remaining arguments are _$replaceEmpty_ and _$replaceInvalid_. In case _$replaceEmpty_ is set to TRUE (default behavior), default value is used for the field in case no/empty value was specified in input. In case _$replaceInvalid_ is set to TRUE (defaults to FALSE), then, should the input value fail any validation, it is replaced silently with the default, and no error is raised.

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

In the last case, _setMaxLength(100)_ limits the length of every string in the _education_ array, while _setArrayMinLength()_ and _setArrayMaxLength()_ set the limits for the array size (so we expect from 0 to 3 entries in education, each entry being a string with a maximum length of 100 chars).

## Callbacks as validators

This is the most powerful validation:

```php
Field::string("username")
    ->addCallbackRule(
        function($value, FieldSet $fieldSet) {
            return !Users::usernameExists($value);
        }
    );
```

When the callback is called, it is passed 3 arguments:

1. $value &mdash; obviously, the value to be validated;
1. $fieldSet &mdash; the FieldSet instance, so that it's possible to get values of neighboring fields;
1. $key &mdash; in case the field expects an [array value](#validating-arrays), then an integer or a string key of the array element is passed (defaults to NULL).

The callback should return boolean: TRUE if the validation passes, FALSE if it fails.

## Dates

Date and datetime fields will transform the input value to a formatted date, so when you finally call _getFiltered()_, they will contain string dates, formatted according to output format setting (_Y-m-d_ by default for date and _Y-m-d H:i:s_ for datetime fields). You may alter this format _setOutputFormat()_. By default, Date and Datetime fields expect the input to strictly follow format set by _setInputFormat()_, and this input format also defaults to _Y-m-d_ or _Y-m-d H:i:s_. However, _setInputFormat()_ accepts the second optional argument &mdash; _$strict_. Setting it to false will cause the validator to try first the exact input format but then to try php's [date_create()](http://php.net/date_create). Be careful with using non-strict date formats, because it might be very confusing. For example, values like "+1year" or "16 years ago march 1st" will be valid dates. Moreover, values like "2010-02-31", which is obviously an invalid date, will pass validation! In the latter case, the resulting value will be (surprise!) **2010-03-03**. More on this in [PHP manual](http://php.net/manual/en/datetime.createfromformat.php) &mdash; read comment by thomas dot ribiere at allgoob dot com.

## Validating arrays

Any field can be set to expect an array value:

```php
Field::int("subscriptions")->expectArray();
```

In this case, the field is expected to be array of elements, each of those validated against type (_int_ in the example above) and any of the rules that you specify. Minimum and maximum array length can be controlled with:

* setArrayMinLength(int)
* setArrayMaxLength(int)

In case some of the array elements fail validation, it might be handy to see what particular elements failed. Consider the following example: 

```php
Field::int("subscriptions", "{label}: value #{key} is not an integer")
    ->setMin(1, "Subscriptions: value #{key} must be greater then or equal to {min}")
    ->expectArray()
    ->setArrayKeyOffset(1);
```

Let's say we have the following dataset:

```json
{
  "subscriptions": [
    1,
    "not an integer",
    -1
  ]
}
```

Then we will have the following error messages:

* subscriptions: value #2 is not an integer"
* subscriptions: value #3 must be greater then or equal to {min}"

The message parser will replace "{key}" with the corresponding array key. Numeric arrays in PHP are zero-indexed, so by default, in case of an error on the first array element, you get an error message saying "value #0 is invalid". While this might be a desired behavior, you also might want this to be displayed as "value #1 is invalid". This is achieved by calling
_Field::setArrayKeyOffset(1)_ - then all the keys are incremented by 1 in the error messages. It is also often the case that integer IDs are used as keys for various data, so by default _validity_ does not apply an offset to numeric keys.

## Validating compound values

Assoc (_Field::**assoc**()_) is a compound field, so _FieldSet_ will expect the value to be an array, which it will validate with a help of _$innerFieldSet_. A real-life example: set of three fields used to make up a date input: _date\[year\]_, _date\[month\]_, _date\[day\]_. Let's suppose that we expect \$_POST\[\"date\"\] to be array(year: integer, month: integer, day: integer) In this case, _$innerFieldSet_ will contain fields, created with _Field::int()_ and named _year_, _month_ and _day_, each having proper range validation: _setMax()_ and _setMin()_. _$message_ parameter to Field::**assoc**() will only be used in case when \$_POST\[\"date\"\] is not an array. If it is an array, then the _$innerFieldSet_ will take care of further validation. Should the _$innerFieldSet_ report any errors, they will all be combined into a string and used as an error message for the _date_ field in the outer _FieldSet_.

## Tips &amp; tricks

* Are you using [Zend Framework](http://zendframework.com/) but still want to use validity? Good news for you! Zend Validators are callable, so you can simply add them as validation rules:

```php
Field::string("email")->addCallbakRule(new EmailAddress(), "Email is invalid!");
```

* Are you using [Yii2](http://www.yiiframework.com/) but still want to use validity? It's just a tiny bit more complicated then in case of ZF:

```php
Field::string("email")->addCallbakRule(function($value) {
    return (new EmailValidator())->validate($value);
}, "Email is invalid!");
```

I chose email validation for this tips on  purpose. Validity also offers email validation, but (at least for now) it is a simple regexp check. Zend or Yii2 offer much more sophisticated ways to validate an email, and you can simply use it while at the same time performing routines validity-style.

## Labels

Every field must have a name. Name is the first parameter to all the [named constructors](#creating-fields). Name is essentially the key of the associative array the _FieldSet_ will validate. In addition, field can also have a label. For example, field name is _date_of_birth_ but label is _Date of birth_. Label can be set with _Field->setLabel(string $label)_. If not set, field name is used as label.