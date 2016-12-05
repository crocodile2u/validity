<?php

namespace validity;

use validity\field\Assoc;
use validity\field\Boolean;
use validity\field\Date;
use validity\field\Datetime;
use validity\field\Double;
use validity\field\Enum;
use validity\field\Integer;
use validity\field\Str;

class Field
{
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
    protected $typeMessage;
    protected $typeMessageKey;
    private $expectArray = false;
    private $expectArrayMessage;
    private $arrayMinLength = 0;
    private $arrayMaxLength = null;
    private $arrayMinMessage;
    private $arrayMaxMessage;
    private $arraySkipEmpty = true;
    /**
     * @var Language|null
     */
    private $language;

    private $default;
    private $defaultReplaceEmpty = false;
    private $defaultReplaceInvalid = false;

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

    private static $arrays = array(
        self::ASSOC,
    );

    private static $allowArrays = array(
        self::ANY,
    );

    /** @var Report */
    private $report;
    protected $currentValue;
    private $valueExists = false;
    private $valueEmpty = true;

    private $filters = array();

    /**
     * @param string $name
     * @param string|null $message
     * @return Integer
     */
    public static function int(string $name, $message = null): Integer
    {
        return new Integer($name, $message);
    }

    /**
     * @param string $name
     * @param string|null $message
     * @return Double
     */
    public static function float(string $name, $message = null): Double
    {
        return new Double($name, $message);
    }

    /**
     * @param string $name
     * @param string|null $message
     * @return Boolean
     */
    public static function bool(string $name, $message = null): Boolean
    {
        return new Boolean($name, $message);
    }

    /**
     * @param string $name
     * @param string|null $message
     * @return Str
     */
    public static function string(string $name, $message = null): Str
    {
        return new Str($name, $message);
    }
    /**
     * @param string $name
     * @param string|null $message
     * @return Date
     */
    public static function date(string $name, $message = null): Date
    {
        return new Date($name, $message);
    }
    /**
     * @param string $name
     * @param string|null $message
     * @return Datetime
     */
    public static function datetime(string $name, $message = null): Datetime
    {
        return new Datetime($name, $message);
    }

    /**
     * @param string $name
     * @param array $values
     * @param string|null $message
     * @return Enum
     */
    public static function enum(string $name, array $values, $message = null): Enum
    {
        return new Enum($name, $values, $message);
    }

    /**
     * @param string $name
     * @param string $message
     * @return Field
     */
    public static function email(string $name, $message = null): Str
    {
        return (new Str($name, null))->addRegexpRule(
            "~^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}\~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$~",
            $message,
            Language::EMAIL_EXPECTED
        );
    }

    /**
     * @param string $name
     * @param int $minLength
     * @param string $message
     * @return Str
     */
    public static function phone(string $name, $minLength = 7, $message = null): Str
    {
         return (new Str($name, null))->addFilter(
            function($value) {
                return preg_replace('/[+() -]/', '', $value);
            }
        )->addRegexpRule('/^\d{' . $minLength . ',20}$/', $message, Language::PHONE_EXPECTED);
    }

    /**
     * @param string $name
     * @param string $pattern
     * @param string $message
     * @return Str
     */
    public static function pattern(string $name, string $pattern, $message = null): Str
    {
        return (new Str($name, null))->addRegexpRule($pattern, $message);
    }

    /**
     * @param string $name
     * @param FieldSet $innerFieldSet
     * @param null $message
     * @param string $errorSeparator
     * @return Assoc
     */
    public static function assoc(string $name, FieldSet $innerFieldSet, $message = null, $errorSeparator = "; "): Assoc
    {
        return new Assoc($name, $innerFieldSet, $message, $errorSeparator);
    }

    /**
     * @param $name string
     * @return Field
     */
    public static function any($name): Field
    {
        return new self($name, self::ANY, null);
    }

    /**
     * Field constructor.
     * @param $name
     * @param $type
     * @param $typeMessage
     */
    protected function __construct($name, $type, $typeMessage)
    {
        $this->name = $name;
        $this->type = $type;
        $this->typeMessage = $typeMessage;
        $this->addRule(
            function($value, Report $report) {
                $value = $this->castToType($value);
                if (null === $value) {
                    return false;
                } else {
                    $report->setFiltered($this->getName(), $value);
                    return true;
                }
            },
            $typeMessage,
            $this->typeMessageKey
        );
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
    public function setLanguage(Language $language): Field
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @param Language $language
     * @return Field
     */
    public function setLanguageIfNotYet(Language $language): Field
    {
        return $this->language ? $this : $this->setLanguage($language);
    }

    /**
     * @return Language
     */
    public function getLanguage(): Language
    {
        return $this->language ?: Language::createDefault();
    }

    /**
     * @param mixed $value
     * @param bool $replaceEmpty
     * @param bool $replaceInvalid
     * @return Field
     */
    public function setDefault($value, $replaceEmpty = true, $replaceInvalid = false): Field
    {
        $this->default = $value;
        $this->defaultReplaceEmpty = $replaceEmpty;
        $this->defaultReplaceInvalid = $replaceInvalid;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param callable $callback
     * @param string|null $message
     * @param int $messageKey
     * @param array $messageData
     * @return Field
     */
    public function addCallbackRule(callable $callback, $message = null): Field
    {
        return $this->addRule($callback, $message);
    }

    /**
     * @param callable $callback
     * @return Field
     */
    public function addFilter($callback): Field
    {
        if (is_callable($callback)) {
            $this->filters[] = $callback;
        } else {
            throw new \InvalidArgumentException(__METHOD__ . " expects 1st argument to be a valid callback");
        }
        return $this;
    }

    /**
     * @param Report $Report
     * @return bool
     */
    public function isValid(Report $Report): bool
    {
        $this->report = $Report;
        $this->currentValue = $this->extractValue();
        $check = $this->checkRequired();
        $check = ($check && $this->checkArray());
        $check = ($check && $this->applyFilters());
        $check = ($check && $this->checkRules());
        $check = ($check && $this->setFilteredValue());
        if ($check) {
            return true;
        } elseif ($this->defaultReplaceInvalid) {
            $this->currentValue = $this->default;
            $this->report->resetErrors($this->name);
            $this->setFilteredValue();
            return true;
        } else {
            $this->setFilteredValue();
            return false;
        }
    }

    /**
     * @param string|null $message
     * @return Field
     */
    public function setRequired($message = null): Field
    {
        $this->required = true;
        $this->requiredMessage = $message;
        return $this;
    }

    /**
     * @param bool|callable $condition
     * @param null $message
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setRequiredIf($condition, $message = null): Field
    {
        if (is_bool($condition)) {
            $this->required = $condition;
        } elseif (is_callable($condition)) {
            $this->required = true;
            $this->requiredCallback = $condition;
        } else {
            throw new \InvalidArgumentException(__METHOD__ . ' expects 1st argument to be NULL, boolean or a valid callback');
        }
        $this->requiredMessage = $message;
        return $this;
    }

    /**
     * @param string|null $message
     * @return Field
     */
    public function expectArray($message = null): Field
    {
        $this->expectArray = true;
        $this->expectArrayMessage = $message;
        return $this;
    }

    /**
     * @param int $threshold
     * @param string $message
     */
    public function setArrayMinLength(int $threshold, $message = null)
    {
        $this->arrayMinLength = $threshold;
        $this->arrayMinMessage = $message;
        return $this;
    }

    /**
     * @param int $threshold
     * @param string $message
     */
    public function setArrayMaxLength(int $threshold, $message = null)
    {
        $this->arrayMaxLength = $threshold;
        $this->arrayMaxMessage = $message;
        return $this;
    }

    /**
     * @param bool $flag
     */
    public function setArraySkipEmpty(bool $flag)
    {
        $this->arraySkipEmpty = $flag;
        return $this;
    }

    /**
     * @param array $spec
     * @return $this
     */
    protected function addRule(callable $callback, $message = null, $messageKey = null, array $messageData = []): Field
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(__METHOD__ . " expects argument 1 to be a valid callback");
        }
        $this->rules[] = [$callback, $message, $messageKey, $messageData];
        return $this;
    }

    /**
     * @return bool
     */
    private function checkRequired(): bool
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
        } elseif (!$this->isRequiredByCondition()) {
            return true;
        } else {
            $this->addError($this->smartMessage($this->requiredMessage, Language::REQUIRED));
            return false;
        }
    }

    /**
     * @return bool
     */
    private function isRequiredByCondition()
    {
        return $this->requiredCallback
            ? (bool) call_user_func_array($this->requiredCallback, array($this->report))
            : true;
    }

    /**
     * @return bool|null
     */
    private function checkArray(): bool
    {
        if ($this->valueEmpty) {
            return true;
        }

        if ($this->expectArray) {
            if (!is_array($this->currentValue)) {
                $this->addError($this->getArrayMessage());
                return false;
            }

            if ($this->arraySkipEmpty) {
                $this->currentValue = array_filter($this->currentValue);
            }

            if ($this->arrayMinLength || $this->arrayMaxLength) {
                $length = count($this->currentValue);
                if ($this->arrayMinLength && ($length < $this->arrayMinLength)) {
                    $this->addError(
                        $this->smartMessage($this->arrayMinMessage, Language::ARRAY_MIN_LEN, ["min" => $this->arrayMinLength])
                    );
                    return false;
                }

                if ($this->arrayMaxLength && ($length > $this->arrayMaxLength)) {
                    $this->addError(
                        $this->smartMessage($this->arrayMaxMessage, Language::ARRAY_MAX_LEN, ["max" => $this->arrayMaxLength])
                    );
                    return false;
                }
            }

            return true;
        } elseif (in_array($this->type, self::$arrays)) {
            if (!is_array($this->currentValue)) {// value MUST BE an array
                $this->addError($this->getArrayMessage());
                return false;
            }
        } elseif (!in_array($this->type, self::$allowArrays) && !is_scalar($this->currentValue)) {// value MUST BE a scalar
            $this->addError($this->predefinedMessage(Language::SCALAR_EXPECTED));
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function checkRules(): bool
    {
        if ($this->valueEmpty) {
            return true;
        }

        foreach ($this->rules as $spec) {
            list($callback, $message, $messageKey, $messageData) = $spec;
            $check = $this->checkSingleRule($callback, $message, $messageKey, $messageData);
            if (!$check) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return mixed
     */
    protected function castToType($value)
    {
        return $value;
    }

    /**
     * @return mixed
     */
    private function extractValue()
    {
        $this->valueExists = false;
        $this->valueEmpty = true;
        $data = $this->report->getRaw();
        if (!is_array($data)) {
            $this->report->addError($this->name, "Data is not an array");
            return null;
        }
        if (array_key_exists($this->name, $data)) {
            $this->valueExists = true;
            return $this->filterValue($data[$this->name]);
        } else {
            return null;
        }
    }

    /**
     * @param $message
     * @return null
     */
    protected function addError($message)
    {
        return $this->report->addError($this->name, $message);
    }

    /**
     * @param $value
     * @return array|string
     */
    public function filterValue($value)
    {
        if (is_string($value)) {
            $preFiltered = $this->preFilterStringValue($value);
            if ($this->valueEmpty) {
                $this->valueEmpty = (0 == strlen($preFiltered));
            }
            return $preFiltered;
        } elseif (is_scalar($value)) {
            $this->valueEmpty = false;
            return $value;
        } elseif (is_null($value)) {
            return $value;
        } else {
            $this->valueEmpty = false;
            return array_map(
                function($value) {
                    return $this->filterValue($value);
                },
                $value
            );
        }
    }

    protected function preFilterStringValue($value)
    {
        return $value;
    }

    private function checkSingleRule(callable $callback, $message, $messageKey, $messageData)
    {
        if ($this->expectArray) {
            $ret = true;
            $validValues = [];
            foreach ($this->currentValue as $key => &$value) {
                $value = $this->checkCallback($value, $callback, $message, $messageKey, $messageData, $key);
                if (null === $value) {
                    $ret = false;
                } else {
                    $validValues[$key] = $value;
                }
            }
            $this->report->setFiltered($this->name, $validValues);
            return $ret;
        } else {
            $this->currentValue = $this->checkCallback($this->currentValue, $callback, $message, $messageKey, $messageData);
            $this->report->setFiltered($this->name, $this->currentValue);
        }
        return $this->report->isOk($this->name);
    }

    private function checkCallback($value, $callback, $message, $messageKey, $messageData, $key = null)
    {
        $messageKey = $messageKey ?: Language::FIELD_FAILED_VALIDATION;
        $messageData = $messageData ?: [];
        $message = $this->smartMessage($message, $messageKey, $messageData);
        $result = call_user_func_array($callback, [$value, $this->report, $key]);
        if ($result) {
            return $this->report->getFiltered($this->name);
        } else {
            return $this->report->addError($this->name, $message);
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
        $this->report->setFiltered($this->name, $this->currentValue);
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

    protected function smartMessage($template, $key, array $data = [])
    {
        return $template
            ? $this->arbitraryMessage($template, $data)
            : $this->predefinedMessage($key, $data);
    }

    protected function arbitraryMessage($template, array $data = [])
    {
        return $this->getLanguage()->message($template, $data + ["label" => $this->getLabel()]);
    }

    protected function predefinedMessage($key, array $data = [])
    {
        return $this->getLanguage()->translate($key, $data + ["label" => $this->getLabel()]);
    }

    /**
     * @param int $type
     * @return $this
     */
    protected function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
