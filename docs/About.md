# Validation of input with validity

> Validity is a simple validation and filtration package for PHP. It can be used to validate both single values like integers and strings, and complex nested structures, using however complex validation logic you can think of. Nice default errors messages in English or Russian. Easily extensible with a custom language package, with always having ability to provide a custom error message for any rule on every field.

## In $_GET we trust

Trustful developer is a bad developer. NO.USER.INPUT.SHOULD.BE.TRUSTED. There's a lot of PHP code that is written with an implication that a certain input parameter contains a certain type of information. For example:

```php
$searchQuery = trim($_GET["query"]);
```

Sometimes it can look a little better:

```php
$searchQuery = trim($_GET["query"] ?? "");
```

...or:

```php
$searchQuery = empty($_GET["query"]) ? "" : trim($_GET["query"]);
```

What if I simply tweak the URL in address bar? The address that used to be "http://example.com/search.php?query=hack" becomes "http://example.com/search.php?query%5B%5D=hack". **%5B%5D** is actually square braces &mdash; **[]**. After this, _$_GET\[\"query\"\]_, that used to be a string, suddenly becomes an array, and all the examples above start to produce an error of level E_WARNING: PHP Warning:  trim() expects parameter 1 to be string, array given. This may or may not have any serious consequence, but is in any case undesirable.

In order to check for a proper value, you could have used this code:

```php
$searchQuery = filter_input(INPUT_GET, "query", FILTER_SANITIZE_STRING);
```

In fact, if you only need to check if the input valid, based on simple type rules, while at the same ensuring that the data is secure, PHP's [filter extension](http://php.net/manual/en/book.filter.php) is certainly worth checking. Simple checks like INT/FLOAT/BOOLEAN are really simple. However, when your validation rules become more sophisticated, you will probably find that filter functions have a messy interface with the need to specify all the parameters in either "options" or "flags" key of the _$options_ argument. Also, most of the time, its the user who provides an invalid input, and most of them do it unintentionally, so it would be nice of you to inform about validation errors in a way that they can understand. Preferably, in a language of a user choice. Filter functions just don't do that. They only can return FALSE when the input is incorrect.

Every major PHP framework has a solution for this problem: [Zend Framework](https://docs.zendframework.com/zend-validator/), [Yii2](http://www.yiiframework.com/doc-2.0/guide-input-validation.html), [Symfony](https://symfony.com/doc/current/validation.html), [Phalcon](https://symfony.com/doc/current/validation.html)

Personally, I don't like any of them.

* Zend Framework offers robust  validation utilities. It's awesome that you can just install the validator package with composer and use it. In my opinion, though, it's a bit overcomplicated, and has too many dependencies (only 2 mandatory though, but some validators will require additional packages). Internationalization is provided in different packages as well, and, in my opinion, is also complicated. I also had a lot of weird issues with Zend Validators back in the times of ZF version 1, and those simply make me avoid ZF where possible. Other then that, it seems to be a cool project, you just have to know a few tips: 1) Zend Validators have fluent setter methods for most of the options, which makes them so much easier to use than instantiating with ugly _$options_ (the way that is promoted in documentation); and 2) they are callable.
* Yii2 also has validators in place. I completely dislike the way they declare validation rules for the models (in form of associative arrays). Ad-Hoc validators do not seem to have a nice interface, I just don't like things like this: _$model->addRule(\['name', 'email'\], 'string', \['max' => 128\])_. Adding custom error messages also seems messy.
* Symfony requires 2 classes to validate a single value: _Constraint_ and _ConstraintValidator_. To validate email, those would be _Email_ and _EmailValidator_. I could not find an easy way to invoke validation on a variable and stopped looking in this direction.
* Phalcon offers a lot of validators. However, the validate method signature is such: _validate (Phalcon\Validation $validation, mixed $field)_. No way to check a single rule without having to instantiate a _Validation_ object. Adding multiple rules to the same field is also not good in my opinion: you have to call _Validation->add('field',..)_ for every rule. _Validator->setOption()_ method does not provide "fluent" interface, so it is not chainable.

## Yet another package

Diversity is good. I made validity, I use it for my projects, I find it convenient - that is why I decided to add it to [Github](https://github.com/crocodile2u/validity) and [Packagist](https://packagist.org/packages/crocodile2u/validity). It is a reasonably small validation package that easily covers all my needs:

* validating basic data types, without having to type a lot,
* validating arrays, validating against complex logic,
* validating values depending on other fields' values,
* filtering of value
* internationalization, nice error messages for all cases &mdash; out-of-the-box
* ability to specify custom message for each and every case

## Take me to the code

OK. You asked for it.


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

Every field must have a name. Name is the first and required parameter to all the [named constructors](#creating-fields). Name is essentially the key of the associative array the _FieldSet_ will validate. In addition, field can also have a label. For example, field name is _date_of_birth_ but label is _Date of birth_. Label can be set with _Field->setLabel(string $label)_. If not set, field name is used as label.

## Conclusion

Validity is one of many validation packages out there. I find it feature-reach enough to build on top of it, and I find it one the most convenient of its class. If you want to help this project: 1) find bugs, submit pull requests (preferable unit-tested) or 2) submit new translations - see validity\\Language\\En.php. Cheers!