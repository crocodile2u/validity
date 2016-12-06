<?php

namespace validity;

use validity\field\Any;
use validity\field\Assoc;
use validity\field\Boolean;
use validity\field\Date;
use validity\field\Datetime;
use validity\field\Double;
use validity\field\Email;
use validity\field\Enum;
use validity\field\Integer;
use validity\field\Phone;
use validity\field\Str;
use validity\field\Url;

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
    /**
     * @var FieldSet
     */
    private $ownerFieldSet;

    private $required = false;
    private $requiredMessage;
    private $requiredCallback;
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

    private $rules = array();

    /** @var Report */
    private $report;
    protected $currentValue;
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
        return new Email($name, $message);
    }

    /**
     * @param string $name
     * @param int $minLength
     * @param string $message
     * @return Str
     */
    public static function phone(string $name, int $minLength = 7, $message = null): Str
    {
         return new Phone($name, $minLength, $message);
    }

    /**
     * @param string $name
     * @param int $minLength
     * @param string $message
     * @return Url
     */
    public static function url(string $name, $message = null): Url
    {
        return new Url($name, $message);
    }

    /**
     * @param string $name
     * @param string $pattern
     * @param string $message
     * @return Str
     */
    public static function pattern(string $name, string $pattern, $message = null): Str
    {
        return (new Str($name, $message))->addRegexpRule($pattern, $message);
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
     * @return Any
     */
    public static function any($name): Any
    {
        return new Any($name);
    }

    /**
     * Field constructor.
     * @param $name
     * @param $type
     * @param $message
     */
    protected function __construct($name, $message)
    {
        $this->name = $name;
        $this->addRule(
            function($value, FieldSet $fieldSet) {
                $value = $this->castToType($value);
                if (null === $value) {
                    return false;
                } else {
                    $fieldSet->getLastReport()->setFiltered($this->getName(), $value);
                    return true;
                }
            },
            $message,
            $this->typeMessageKey
        );
    }

    /**
     * @return FieldSet
     */
    public function getOwnerFieldSet(): FieldSet
    {
        if (null === $this->ownerFieldSet) {
            $this->ownerFieldSet = new FieldSet();
        }
        return $this->ownerFieldSet;
    }

    /**
     * @param FieldSet $ownerFieldSet
     * @return Field
     */
    public function setOwnerFieldSet(FieldSet $ownerFieldSet): Field
    {
        $this->ownerFieldSet = $ownerFieldSet;
        return $this;
    }

    public function getFiltered()
    {
        return $this->getReport()->getFiltered($this->getName());
    }

    public function getRaw()
    {
        return $this->getReport()->getRaw($this->getName());
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
     * @return Language
     */
    public function getLanguage(): Language
    {
        return $this->language ?: $this->getOwnerFieldSet()->getLanguage();
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
     * @return $this
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
    public function isValid($value): bool
    {
        $this->getReport()->resetErrors($this->name);
        $check = $this->checkRequired($this->preFilter($value));
        $check = ($check && $this->checkArray());
        $check = ($check && $this->applyFilters());
        $check = ($check && $this->checkRules());
        $check = ($check && $this->updateFilteredValue());
        if ($check) {
            return true;
        } elseif ($this->defaultReplaceInvalid) {
            $this->currentValue = $this->default;
            $this->getReport()->resetErrors($this->name);
            $this->updateFilteredValue();
            return true;
        } else {
            $this->updateFilteredValue();
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
    public function checkRequired($value): bool
    {
        $this->currentValue = $value;
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

        if ($ret) {
            $this->valueEmpty = false;
            $this->updateFilteredValue();
            return true;
        } elseif ($this->defaultReplaceEmpty) {
            $this->valueEmpty = false;
            $this->currentValue = $this->default;
            $this->updateFilteredValue();
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
            ? (bool) call_user_func_array($this->requiredCallback, array($this->getOwnerFieldSet()))
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
        } elseif ($this->isArray()) {
            if (!is_array($this->currentValue)) {// value MUST BE an array
                $this->addError($this->getArrayMessage());
                return false;
            }
        } elseif (!$this->allowsArray() && !is_scalar($this->currentValue)) {// value MUST BE a scalar
            $this->addError($this->predefinedMessage(Language::SCALAR_EXPECTED));
            return false;
        }

        return true;
    }

    protected function allowsArray()
    {
        return false;
    }

    protected function isArray()
    {
        return false;
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
     * @param $message
     * @return null
     */
    protected function addError($message)
    {
        return $this->getReport()->addError($this->name, $message);
    }

    /**
     * @param $value
     * @return array|string
     */
    public function preFilter($value)
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
                    return $this->preFilter($value);
                },
                $value
            );
        }
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function preFilterStringValue($value)
    {
        return $value;
    }

    /**
     * @param callable $callback
     * @param string $message
     * @param int $messageKey
     * @param array $messageData
     * @return bool
     */
    private function checkSingleRule(callable $callback, $message, $messageKey, $messageData)
    {
        $report = $this->getReport();
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
            $report->setFiltered($this->name, $validValues);
            return $ret;
        } else {
            $this->currentValue = $this->checkCallback($this->currentValue, $callback, $message, $messageKey, $messageData);
            $report->setFiltered($this->name, $this->currentValue);
        }
        return $report->isOk($this->name);
    }

    /**
     * @param mixed $value
     * @param callable $callback
     * @param string $message
     * @param int $messageKey
     * @param array $messageData
     * @param null|string|int $key
     * @return mixed|null
     */
    private function checkCallback($value, $callback, $message, $messageKey, $messageData, $key = null)
    {
        $messageKey = $messageKey ?: Language::FIELD_FAILED_VALIDATION;
        $messageData = $messageData ?: [];
        if (null !== $key) {
            $messageData["key"] = is_int($key) ? ($key + 1) : $key;
        }
        $message = $this->smartMessage($message, $messageKey, $messageData);
        $result = call_user_func_array($callback, [$value, $this->getOwnerFieldSet(), $key]);
        if ($result) {
            return $this->getOwnerFieldSet()->getFiltered($this->name);
        } else {
            return $this->getReport()->addError($this->name, $message, $key);
        }
    }

    /**
     * @return string
     */
    private function getArrayMessage()
    {
        $data = ["min" => $this->arrayMinLength, "max" => $this->arrayMaxLength];
        return $this->expectArrayMessage
            ? $this->arbitraryMessage($this->expectArrayMessage, $data)
            : $this->predefinedMessage(Language::ARRAY_EXPECTED);
    }

    /**
     * @return bool
     */
    protected function updateFilteredValue()
    {
        $this->getReport()->setFiltered($this->name, $this->currentValue);
        return true;
    }

    /**
     * @return bool
     */
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

    /**
     * @param mixed $value
     * @return mixed
     */
    private function applyFiltersToValue($value)
    {
        $ret = $value;
        foreach ($this->filters as $filter) {
            $ret = call_user_func($filter, $ret);
        }
        return $ret;
    }

    /**
     * @param string $template
     * @param int $key
     * @param array $data
     * @return string
     */
    protected function smartMessage($template, $key, array $data = [])
    {
        return $template
            ? $this->arbitraryMessage($template, $data)
            : $this->predefinedMessage($key, $data);
    }

    /**
     * @param string $template
     * @param array $data
     * @return mixed
     */
    protected function arbitraryMessage($template, array $data = [])
    {
        return $this->getLanguage()->message($template, $data + ["label" => $this->getLabel()]);
    }

    /**
     * @param int G$key
     * @param array $data
     * @return mixed
     */
    protected function predefinedMessage($key, array $data = [])
    {
        return $this->getLanguage()->translate($key, $data + ["label" => $this->getLabel()]);
    }

    /**
     * @return Report
     */
    protected function getReport()
    {
        return $this->getOwnerFieldSet()->getLastReport();
    }
}
