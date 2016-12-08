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

Every major PHP framework has a solution for this problem. [Zend Framework](https://docs.zendframework.com/zend-validator/), [Yii2](http://www.yiiframework.com/doc-2.0/guide-input-validation.html), [Symphony](https://symfony.com/doc/current/validation.html), [Phalcon](https://symfony.com/doc/current/validation.html)

Personally, I don't like any of them.

* Zend Framework offers robust  validation utilities. It's awesome that you can just install the validator package with composer and use it. In my opinion, though, it's a bit overcomplicated, and has too many dependencies (only 2 mandatory though, but some validators will require additional packages). Internationalization is provided in different packages as well, and, in my opinion, is also complicated. I also had a lot of weird issues with Zend Validators back in the times of ZF version 1, and they simply make me avoid ZF where possible. Other then that, it seems to be a cool project, you just have to know a few tips: 1) Zend Validators have fluent setter methods for most of the options, which makes them so much easier to use than instantiating with ugly _$options_ (the way that is promoted in documentation); and 2) they are callable.
* Yii2 also has validators in place. I completely dislike the way they declare validation rules for the models (in form of associative arrays). Ad-Hoc validators do not seem to have a nice interface, I just don't like things like this: _$model->addRule(\['name', 'email'\], 'string', \['max' => 128\])_. Adding custom error messages also seems messy.
* Symphony requires 2 classes to validate a single value: _Constraint_ and _ConstraintValidator_. To validate email, those would be _Email_ and _EmailValidator_. I could not find an easy way to invoke validation on a variable and stopped looking in this direction.
* Phalcon offers a lot of validators. However, the validate method signature is such: _validate (Phalcon\Validation $validation, mixed $field)_. No way to check a single rule without having to instantiate a _Validation_ object. Adding multiple rules to the same field is also not good in my opinion: you have to call _Validation->add('field',..)_ for every rule. _Validator->setOption()_ method does not provide "fluent" interface, so it is not chainable.