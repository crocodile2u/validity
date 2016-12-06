<?php

namespace validity\language;

use validity\Language;

class Ru extends Language
{
    protected $messages = [
        self::REQUIRED => "{label} необходимо заполнить",
        self::ARRAY_EXPECTED => "Поле {label} должно содержать массив",
        self::SCALAR_EXPECTED => "Поле {label} должно содержать скаляр",
        self::ILLEGAL_CHAR => "Поле {label} содержит недопустимые символы",
        self::INT => "Поле {label} должно содержать целое число",
        self::NUMBER_MIN => "Минимальное значение поля {label} - {min}",
        self::NUMBER_MAX => "Максимальное значение поля {label} - {max}",
        self::STRING => "Поле {label} должно содержать строку",
        self::STRING_MIN_LEN => "Минимальная длина поля {label} - {min}",
        self::STRING_MAX_LEN => "Максимальная длина поля {label} - {max}",
        self::FLOAT => "Поле {label} должно содержать число с плавающей точкой",
        self::BOOL => "Поле {label} должно содержать булево значение",
        self::FIELD_FAILED_VALIDATION => "Поле {label} не прошло валидацию",
        self::ASSOC_EXPECTED => "Поле {label} должно содержать ассоциативный массив",
        self::DATETIME_EXPECTED => "Поле {label} должно содержать дату и время",
        self::DATE_EXPECTED => "Поле {label} должно содержать дату",
        self::EMAIL_EXPECTED => "Поле {label} должно содержать адрес электронной почты",
        self::PHONE_EXPECTED => "Поле {label} должно содержать номер телефона",
        self::REGEXP_VALIDATION_FAILED => "Поле {label} не прошло валидацию по шаблону",
        self::ENUM_VALIDATION_FAILED => "Поле {label} должно содержать одно из значений: {values}",
        self::DATE_MIN => "Поле {label} не должно быть раньше {min}",
        self::DATE_MAX => "Поле {label} не должно быть позднее {max}",
        self::ARRAY_MIN_LEN => "Поле {label} должно содержать как минимум {min} элементов",
        self::ARRAY_MAX_LEN => "Поле {label} должно содержать максимум {max} элементов",
        self::MIN => "Минимальное значение поля {label} - {min}",
        self::MIN => "Максимальное значение поля {label} - {max}",
    ];
}