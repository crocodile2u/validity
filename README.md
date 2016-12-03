# Validity

> Validity is a simple validation and filtration package for PHP.

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
* Out-of-the-box English & Russian languages are at your disposal, containing default error messages for every case. You can easily create your own language class to use for default messages. At least for a boilerplate version of code those message should work fine, without any need to write them by hand.
* Easy to override error messages for each and every validation rule: if you don't specify an error message, then a default one is selected from the language class. If a message is provided for a rule, that message is used.
* When dealing with strings, potentially malicious unicode characters are not allowed, for better security.

## Code example

```php

use \validity\FieldSet, \validity\Field;

$fieldSet = (new FieldSet())
        ->add(
            Field::pattern("name", "/^[A-Z][a-zA-Z\- ]+$/", "Name: only latin letters and spaces, starting with a capital letter")
                ->setRequired()
        )->add(
            Field::enum("greeting", ["mr", "mrs"])->setRequired()
        )->add(
            Field::int("subscriptions", "Subscription ID is not an integer")->setMin(1)->expectArray()
        )->add(
            Field::email("email")->setRequiredIf(
                function($value, \validity\Report $report) {
                    return (bool) $report->getFiltered("subscriptions");
                },
                "Email is required in case you want to subscribe for news"
            )
        )->add(
            Field::date("date_of_birth")->setMax("-18years")->setRequired()
        )->add(
            Field::string("education")
                ->setMinLength(10)
                ->setMaxLength(100)
                ->expectArray()
                ->limitArrayLength(0, 3)
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