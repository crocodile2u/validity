<?php

namespace validity\language;

use validity\Language;

class En extends Language
{
    protected $messages = [
        self::REQUIRED => "{label} is required",
        self::ARRAY_EXPECTED => "{label} is expected to be array",
        self::SCALAR_EXPECTED => "{label} is expected to be scalar",
        self::ILLEGAL_CHAR => "{label} contains illegal characters",
        self::INT => "{label} must be an integer",
        self::NUMBER_MIN => "{label} must have a minimum value of {min}",
        self::NUMBER_MAX => "{label} must have a maximum value of {max}",
        self::STRING => "{label} must be a string",
        self::STRING_MIN_LEN => "{label} must have a minimum length of {min}",
        self::STRING_MAX_LEN => "{label} must have a maximum length of  {max}",
        self::FLOAT => "{label} must be a floating point number",
        self::BOOL => "{label} must be a boolean flag",
        self::FIELD_FAILED_VALIDATION => "{label} failed validation",
        self::ASSOC_EXPECTED => "{label} must be an associative array",
        self::DATETIME_EXPECTED => "{label} has to be a valid date/time string",
        self::DATE_EXPECTED => "{label} has to be a valid date string",
        self::EMAIL_EXPECTED => "{label} has to be a valid email address",
        self::PHONE_EXPECTED => "{label} has to be a valid phone number",
        self::REGEXP_VALIDATION_FAILED => "{label} failed pattern validation",
        self::ENUM_VALIDATION_FAILED => "{label} must be one of the following: {values}",
        self::DATE_MIN => "{label} must not be earlier than {min}",
        self::DATE_MAX => "{label} must not be later than {max}",
        self::ARRAY_MIN_LEN => "{label} must have at least {min} elements",
        self::ARRAY_MAX_LEN => "{label} must have at most {max} elements",
    ];
}