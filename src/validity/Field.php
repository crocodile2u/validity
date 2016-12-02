<?php

namespace validity;

class Field
{
    const DEFAULT_REQUIRED_MESSAGE = "%s field is required";
    const DEFAULT_ARRAY_MESSAGE = "%s field is expected to be array";
    const DEFAULT_SCALAR_MESSAGE = "%s field is expected to be scalar";
    const DEFAULT_ILLEGAL_CHAR_MESSAGE = "%s field contains illegal character";
    const DEFAULT_DATE_FORMAT = "Y-m-d";
    const DEFAULT_DATETIME_FORMAT = "Y-m-d H:i:s";
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $label;
    private $type = self::ANY;
    private $required = false;
    private $requiredMessage;
    private $requiredCallback;
    private $typeMessage;
    private $expectArray = false;
    private $expectArrayMessage;
    private $arrayMinLength = 0;
    private $arrayMaxLength = null;
    /**
     * @var Language|null
     */
    private $language;

    private $default;
    private $defaultReplaceEmpty = false;
    private $defaultReplaceIncorrect = false;

    const INT = 0;
    const FLOAT = 1;
    const STRING = 2;
    const BOOLEAN = 3;
    const CALLBACK = 4;
    const ENUM = 5;
    const ASSOC = 6;
    const ANY = 7;
    const MIN = 8;
    const MAX = 9;
    const MIN_LENGTH = 10;
    const MAX_LENGTH = 11;
    const DATE = 12;
    const DATETIME = 13;

    private $rules = array();

    private static $checkMethodMap = array(
        self::INT => 'checkInt',
        self::STRING => 'checkString',
        self::DATE => 'checkString',
        self::DATETIME => 'checkString',
        self::CALLBACK => 'checkCallback',
        self::BOOLEAN => 'checkBoolean',
        self::FLOAT => 'checkFloat',
        self::ENUM => 'checkEnum',
        self::ASSOC => 'checkAssoc',
        self::MIN => 'checkMinRange',
        self::MAX => 'checkMaxRange',
        self::MIN_LENGTH => 'checkMinLength',
        self::MAX_LENGTH => 'checkMaxLength',
    );

    private static $arrays = array(
        self::ASSOC,
    );

    private static $allowArrays = array(
        self::ANY,
    );

    /** @var Report */
    private $Report;
    private $currentValue;
    private $valueExists = false;
    private $valueEmpty = true;

    private $filters = array();

    private $dateFormat = self::DEFAULT_DATE_FORMAT;
    private $datetimeFormat = self::DEFAULT_DATETIME_FORMAT;

    /**
     * @param string $name
     * @param string|null $message
     * @return Field
     */
    public static function int($name, $message = null)
    {
        return new self($name, self::INT, $message);
    }

    /**
     * @param string $name
     * @param string|null $message
     * @return Field
     */
    public static function float($name, $message = null)
    {
        return new self($name, self::FLOAT, $message);
    }

    /**
     * @param string $name
     * @param string|null $message
     * @return Field
     */
    public static function bool($name, $message = null)
    {
        return new self($name, self::BOOLEAN, $message);
    }

    /**
     * @param string $name
     * @param callable $callback
     * @param string|null $message
     * @return Field
     */
    public static function callback($name, $callback, $message = null)
    {
        return self::any($name)->addCallbackRule($callback, $message);
    }

    /**
     * @param string $name
     * @param string|null $message
     * @return Field
     */
    public static function string($name, $message = null)
    {
        return new self($name, self::STRING, $message);
    }
    /**
     * @param string $name
     * @param string|null $message
     * @return Field
     */
    public static function date($name, $message = null)
    {
        return self::createDateField($name, self::DATE, $message, "dateFormat");
    }
    /**
     * @param string $name
     * @param string|null $message
     * @return Field
     */
    public static function datetime($name, $message = null)
    {
        return self::createDateField($name, self::DATETIME, $message, "datetimeFormat");
    }

    private static function createDateField($name, $type, $message, $formatProperty)
    {
        $field = new self($name, $type, $message);
        $callback = (function($name, $value, $message, Report $Result) use ($field, $formatProperty) {
            if (false === ($ts = strtotime($value))) {
                return $Result->addError($name, $message);
            } else {
                return date($field->$formatProperty, $ts);
            }
        });
        return $field->addCallbackRule($callback, $message);
    }

    /**
     * @param string $name
     * @param array $values
     * @param string|null $message
     * @return Field
     */
    public static function enum($name, $values, $message = null)
    {
        return (new self($name, self::ANY, null))->addCallbackRule(
            function($name, $value, $message, Report $Result) use ($values) {
                if (in_array($value, $values)) {
                    return $value;
                } else {
                    return $Result->addError($name, $message);
                }
            },
            $message
        );
    }

    public static function email($name, $message = null)
    {
        return (new self($name, self::STRING, null))->addRegexpRule(
            "~^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}\~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$~",
            $message
        );
    }

    public static function phone($name, $minLength = 7, $message = null)
    {

         return (new self($name, self::STRING, null))->addFilter(
            function($value) {
                return preg_replace('/[+() -]/', '', $value);
            }
        )->addRegexpRule('/^\d{' . $minLength . ',20}$/', $message);
    }

    public static function pattern($name, $pattern, $message = null)
    {
        return (new self($name, self::STRING, null))->addRegexpRule($pattern, $message);
    }

    public static function assoc($name, FieldSet $innerFieldSet, $message = null, $errorSeparator = "; ")
    {
        return (new self($name, self::ANY, null))->addCallbackRule(
            function($name, $value, $message, Report $Report) use ($innerFieldSet, $errorSeparator) {
                if (!is_array($value)) {
                    return $Report->addError($name, $message);
                }

                if ($innerFieldSet->isValid($value)) {
                    return $innerFieldSet->getFiltered();
                } else {
                    $errors = join($errorSeparator, $innerFieldSet->getErrors()->toPlainArray($errorSeparator));
                    return $Report->addError($name, $errors);
                }
            },
            $message
        );
    }

    /**
     * @param $name string
     * @return Field
     */
    public static function any($name)
    {
        return new self($name, self::ANY, null);
    }

    protected function __construct($name, $type, $typeMessage)
    {
        $this->name = $name;
        $this->type = $type;
        $this->typeMessage = $typeMessage;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label ?: $this->name;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param Language $language
     * @return Field
     */
    public function setLanguage(Language $language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language ?: Language::getInstance();
    }

    /**
     * @param mixed $min
     * @param string $message
     * @return Field
     */
    public function setMin($min, $message = null)
    {
        return $this->addRule([self::MIN, $message, $min]);
    }

    /**
     * @param mixed $max
     * @param string $message
     * @return Field
     */
    public function setMax($max, $message = null)
    {
        return $this->addRule([self::MAX, $message, $max]);
    }

    /**
     * @param mixed $length
     * @param string $message
     * @return Field
     */
    public function setMinLength($length, $message = null)
    {
        return $this->addRule([self::MIN_LENGTH, $message, $length]);
    }

    /**
     * @param mixed $max
     * @param string $message
     * @return Field
     */
    public function setMaxLength($length, $message = null)
    {
        return $this->addRule([self::MAX_LENGTH, $message, $length]);
    }

    /**
     * @param string $dateFormat
     * @return Field
     */
    public function setDateFormat(string $dateFormat): Field
    {
        $this->dateFormat = $dateFormat;
        return $this;
    }

    /**
     * @param string $datetimeFormat
     * @return Field
     */
    public function setDatetimeFormat(string $datetimeFormat): Field
    {
        $this->datetimeFormat = $datetimeFormat;
        return $this;
    }

    /**
     * @param mixed $value
     * @param bool $replace_empty
     * @param bool $replace_incorrect
     * @return Field
     */
    public function setDefault($value, $replace_empty = true, $replace_incorrect = false)
    {
        $this->default = $value;
        $this->defaultReplaceEmpty = $replace_empty;
        $this->defaultReplaceIncorrect = $replace_incorrect;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param callable $callback
     * @param string|null $message
     * @param int $messageKey
     * @return Field
     */
    public function addCallbackRule($callback, $message = null, $messageKey = Language::FIELD_FAILED_VALIDATION)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(__METHOD__ . " expects argument 1 to be a valid callback");
            $callback = function($name, $value, $message, Report $Report) {
                $Report->addError($name, $message);
                return null;
            };
        }
        return $this->addRule(array(self::CALLBACK, $message, $callback, $messageKey));
    }

    /**
     * @param callable $callback
     * @return Field
     */
    public function addFilter($callback)
    {
        if (is_callable($callback)) {
            $this->filters[] = $callback;
        } else {
            throw new \InvalidArgumentException(__METHOD__ . " expects 1st argument to be a valid callback");
        }
        return $this;
    }

    /**
     * @param string|$regexp
     * @param string|null $message
     * @param int $messageKey
     * @return Field
     */
    public function addRegexpRule($regexp, $message = null, $messageKey = Language::REGEXP_VALIDATION_FAILED)
    {
        return $this->addCallbackRule(
            function($name, $value, $message, Report $Report) use ($regexp) {
                if (preg_match($regexp, $value)) {
                    return $value;
                } else {
                    return $Report->addError($name, $message);
                }
            },
            $message,
            $messageKey
        );
    }

    /**
     * @param Report $Report
     * @return bool
     */
    public function isValid(Report $Report)
    {
        $this->Report = $Report;
        $this->currentValue = $this->extractValue();
        $check = $this->checkRequired();
        $check = ($check && $this->checkArray());
        $check = ($check && $this->applyFilters());
        $check = ($check && $this->checkType());
        $check = ($check && $this->checkRules());
        $check = ($check && $this->setFilteredValue());
        if ($check) {
            return true;
        } elseif ($this->defaultReplaceIncorrect) {
            $this->currentValue = $this->default;
            $this->Report->resetErrors($this->name);
            $this->setFilteredValue();
            return true;
        } else {
            $this->Report->setFiltered($this->name, null);
            return false;
        }
    }

    /**
     * @param string|null $message
     * @return Field
     */
    public function setRequired($message = null)
    {
        $this->required = true;
        $this->requiredMessage = $message;
        return $this;
    }

    /**
     * @param bool|callable $condition
     * @param null $message
     * @return $this
     */
    public function setRequiredIf($condition, $message = null)
    {
        if (is_bool($condition)) {
            $this->required = $condition;
        } elseif (is_callable($condition)) {
            $this->requiredCallback = $condition;
        } else {
            throw new \InvalidArgumentException(__METHOD__ . ' expects 1st argument to be NULL, boolean or a valid callback');
        }
        $this->requiredMessage = $message;
        return $this;
    }

    /**
     * @param string|null $message
     * @param int $minLength
     * @param int|null $maxLength
     * @return Field
     */
    public function expectArray($message = null, $minLength = 0, $maxLength = null)
    {
        $this->expectArray = true;
        $this->arrayMinLength = $minLength;
        $this->arrayMaxLength = $maxLength;
        $this->expectArrayMessage = $message;
        return $this;
    }
    /**
     * @param array $spec
     * @return Field
     */
    protected function addRule($spec)
    {
        $this->rules[] = $spec;
        return $this;
    }

    private function checkRequired()
    {
        if (!$this->valueExists) {
            $ret = false;
        } else {
            switch (gettype($this->currentValue)) {
                case 'string':
                    $ret = strlen($this->currentValue) > 0;
                    break;

                case 'array':
                    $ret = count($this->currentValue) > 0;
                    break;

                case 'integer':
                case 'double':
                case 'boolean':
                case 'resource':
                case 'object':
                    $ret = true;
                    break;

                default:
                    $ret = false;
                    break;
            }
        }

        if ($ret) {
            return true;
        } elseif ($this->defaultReplaceEmpty) {
            $this->currentValue = $this->default;
            return true;
        } elseif (!$this->required) {
            return true;
        } elseif (!$this->checkRequiredCondition()) {
            return true;
        } else {
            return $this->addError($this->smartMessage($this->requiredMessage, Language::REQUIRED));
        }
    }

    private function checkRequiredCondition()
    {
        return $this->requiredCallback
            ? call_user_func_array($this->requiredCallback, array($this->currentValue, $this->Report))
            : true;
    }

    private function checkArray()
    {
        if (null === $this->currentValue) {
            return true;
        }

        if ($this->expectArray) {
            if (!is_array($this->currentValue)) {
                return $this->addError($this->getArrayMessage());
            }

            if ($this->arrayMinLength || $this->arrayMaxLength) {
                $length = count($this->currentValue);
                if ($this->arrayMinLength && ($length < $this->arrayMinLength)) {
                    return $this->addError($this->getArrayMessage());
                }

                if ($this->arrayMaxLength && ($length > $this->arrayMaxLength)) {
                    return $this->addError($this->getArrayMessage());
                }
            }

            return true;
        } elseif (in_array($this->type, self::$arrays)) {
            if (!is_array($this->currentValue)) {// value MUST BE an array
                return $this->addError($this->getArrayMessage());
            }
        } elseif (!in_array($this->type, self::$allowArrays) && !is_scalar($this->currentValue)) {// value MUST BE a scalar
            return $this->addError($this->predefinedMessage(Language::SCALAR_EXPECTED));
        }

        return true;
    }

    private function checkAssoc($value, $message, $args)
    {
        if (empty($args)) {
            $error_message = "Validator misconfiguration: expecting DataValidator for a field of type ASSOC";
            return $this->addError($error_message);
        }

        if (!is_array($value)) {
            return $this->addError($this->predefinedMessage(Language::ARRAY_EXPECTED));
        }

        /** @var FieldSet $Validator */
        $Validator = $args[0];
        if ($Validator->isValid($value)) {
            return $Validator->getFiltered();
        } else {
            $errors = $Validator->getErrors();
            if (!empty($args[1])) {// 2nd arg is "merge_errors". Where TRUE, all errors are merged into a single string
                $errors = join("; ", $errors);
            }
            return $this->addError($errors);
        }
    }

    private function checkRules()
    {
        if ($this->valueEmpty) {
            return true;
        }

        foreach ($this->rules as $spec) {
            $type = array_shift($spec);
            $message = array_shift($spec);
            $args = $spec;
            $check = $this->checkSingleRule($type, $message, $args);
            if (!$check) {
                return false;
            }
        }

        return true;
    }

    private function checkType()
    {
        return $this->checkSingleRule($this->type, $this->typeMessage, []);
    }

    private function extractValue()
    {
        $this->valueExists = false;
        $this->valueEmpty = true;
        $data = $this->Report->getRaw();
        if (!is_array($data)) {
            $this->Report->addError($this->name, "Data is not an array");
            return null;
        }
        if (array_key_exists($this->name, $data)) {
            $this->valueExists = true;
            return $this->filterValue($data[$this->name]);
        } else {
            return null;
        }
    }

    private function addError($message)
    {
        return $this->Report->addError($this->name, $message);
    }

    public function filterValue($value)
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($this->valueEmpty) {
                $this->valueEmpty = (0 == strlen($trimmed));
            }
            return $trimmed;
        } elseif (is_scalar($value)) {
            $this->valueEmpty = false;
            return $value;
        } elseif (is_null($value)) {
            return $value;
        } else {
            $this->valueEmpty = false;
            return array_map(array($this, 'filterValue'), $value);
        }
    }

    private function checkSingleRule($type, $message, $args)
    {
        $method = self::$checkMethodMap[$type] ?? null;
        if (!$method) {
            return true;
        }

        if ($this->expectArray) {
            foreach ($this->currentValue as $key => &$value) {
                $value = $this->$method($value, $message, $args, $key);
                if (!$this->Report->isOk($this->name)) {
                    return false;
                }
            }
        } else {
            $this->currentValue = $this->$method($this->currentValue, $message, $args, $this->name);
        }
        return $this->Report->isOk($this->name);
    }

    private function checkInt($value, $message)
    {
        if ($value && ($value[0] === '+')) {
            $value = mb_substr($value, 1);
        }
        if ($value !== '') {
            $value = ltrim($value, '0');
            if ($value === '') {
                $value = '0';
            }
        }
        if ('' === $value) {
            $value = '0';
        }
        if ((string)(int)$value !== $value) {
            return $this->addError($this->smartMessage($message, Language::INT));
        }
        return (int)$value;
    }

    private function checkFloat($value, $message)
    {
        $value = str_replace([',', ' '], ['.', ''], $value);
        if (!is_numeric($value)) {
            return $this->addError($this->smartMessage($message, Language::FLOAT));
        }
        return (float)$value;
    }

    private function checkBoolean($value, $message)
    {
        if (is_bool($value)) {
            return $value;
        } elseif (is_numeric($value) && ((1 == $value) || (0 == $value))) {
            return (bool)$value;
        } else {
            $bool_str = strtoupper($value);

            $yes = ['YES', 'ON', 'TRUE'];
            if (in_array($bool_str, $yes)) {
                return true;
            }

            $no = ['NO', 'OFF', 'FALSE'];
            if (in_array($bool_str, $no)) {
                return false;
            }
        }

        return $this->addError($this->smartMessage($message, Language::BOOL));
    }

    private function checkCallback($value, $message, $args, $key)
    {
        $callback = array_shift($args);
        $messageKey = array_shift($args) ?: Language::FIELD_FAILED_VALIDATION;
        $message = $this->smartMessage($message, $messageKey);
        return call_user_func_array($callback, [$this->name, $value, $message, $this->Report, $key]);
    }

    private function checkString($value, $message, $args)
    {
        if (!is_string($value)) {
            return $this->addError($this->smartMessage($message, Language::STRING));
        }
        $filteredValue = mb_convert_encoding($value, 'utf-8', 'utf-8');
        if ($filteredValue !== $value) {
            return $this->addError($this->predefinedMessage(Language::ILLEGAL_CHAR));
        }
        return $value;
    }

    private function checkEnum($value, $message, $args)
    {
        $values = reset($args);
        if (in_array($value, $values)) {
            return $value;
        } else {
            return $this->addError($this->smartMessage($message, Language::ENUM_VALIDATION_FAILED));
        }
    }

    private function checkMinRange($value, $message, $args)
    {
        $threshold = $args[0];
        switch ($this->type) {
            case self::INT:
            case self::FLOAT:
                $template = Language::NUMBER_MIN;
                $valid = ($value >= $threshold);
                break;
            case self::DATE:
            case self::DATETIME:
                $template = Language::DATE_MIN;
                $valid = (strtotime($value) >= $this->toTimestamp($threshold));
                break;
            default:
                throw new \LogicException("Minimum rule is only applicable to numbers and dates");
        }
        return $valid ? $value : $this->addError($this->smartMessage($message, $template, ["min" => $threshold]));
    }

    private function checkMaxRange($value, $message, $args)
    {
        $threshold = $args[0];
        switch ($this->type) {
            case self::INT:
            case self::FLOAT:
            $template = Language::NUMBER_MAX;
                $valid = ($value <= $threshold);
                break;
            case self::DATE:
            case self::DATETIME:
                $template = Language::DATE_MAX;
                $valid = (strtotime($value) <= $this->toTimestamp($threshold));
                break;
            default:
                throw new \LogicException("Maximum rule is only applicable to numbers and dates");
        }
        return $valid ? $value : $this->addError($this->smartMessage($message, $template, ["max" => $threshold]));
    }

    private function checkMinLength($value, $message, $args)
    {
        if (mb_strlen($value) < $args[0]) {
            return $this->addError($this->smartMessage($message, Language::STRING_MIN_LEN, ["min" => $args[0]]));
        } else {
            return $value;
        }
    }

    private function checkMaxLength($value, $message, $args)
    {
        if (mb_strlen($value) > $args[0]) {
            return $this->addError($this->smartMessage($message, Language::STRING_MAX_LEN, ["max" => $args[0]]));
        } else {
            return $value;
        }
    }

    private function getArrayMessage()
    {
        $data = ["min" => $this->arrayMinLength, "max" => $this->arrayMaxLength];
        return $this->expectArrayMessage
            ? $this->arbitraryMessage($this->expectArrayMessage, $data)
            : $this->predefinedMessage(Language::ARRAY_EXPECTED);
    }

    private function setFilteredValue()
    {
        $this->Report->setFiltered($this->name, $this->currentValue);
        return true;
    }

    private function applyFilters()
    {
        if ($this->valueEmpty) {
            return true;
        }

        if ($this->expectArray) {
            foreach ($this->currentValue as &$value) {
                $value = $this->applyFiltersToValue($value);
            }
        } else {
            $this->currentValue = $this->applyFiltersToValue($this->currentValue);
        }

        return true;
    }

    private function applyFiltersToValue($value)
    {
        $ret = $value;
        foreach ($this->filters as $filter) {
            $ret = call_user_func($filter, $ret);
        }
        return $ret;
    }

    private function toTimestamp($spec)
    {
        if (null === $spec) {
            return null;
        } elseif (is_int($spec)) {
            return $spec;
        } elseif ($spec instanceof \DateTime) {
            return $spec->getTimestamp();
        } elseif (is_string($spec)) {
            if ($ts = strtotime($spec)) {
                return $ts;
            } else {
                throw new \InvalidArgumentException("Cannot convert {label} to timestamp");
            }
        }
    }

    private function smartMessage($template, $key, array $data = [])
    {
        return $template
            ? $this->arbitraryMessage($template, $data)
            : $this->predefinedMessage($key, $data);
    }

    private function arbitraryMessage($template, array $data = [])
    {
        return $this->getLanguage()->message($template, $data + ["label" => $this->getLabel()]);
    }

    private function predefinedMessage($key, array $data = [])
    {
        return $this->getLanguage()->translate($key, $data + ["label" => $this->getLabel()]);
    }
}
