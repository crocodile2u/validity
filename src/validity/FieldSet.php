<?php

namespace validity;

class FieldSet
{
    /** @var Field[] */
    private $fields = array();
    /** @var Report */
    private $lastReport;
    /** @var Language */
    private $language;

    public function __construct(Language $language = null)
    {
        $this->language = $language ?: Language::createDefault();
        $this->lastReport = new Report([]);
    }

    /**
     * @param Field $field
     * @return FieldSet
     */
    public function add(Field $field): FieldSet
    {
        $this->fields[] = $field->setOwnerFieldSet($this);
        return $this;
    }

    /**
     * @return Report
     */
    public function getLastReport(): Report
    {
        return $this->lastReport;
    }

    /**
     * @param $data
     * @return bool
     */
    public function isValid($data): bool
    {
        $this->lastReport = new Report($data);
        $ret = true;
        foreach ($this->fields as $field) {
            $name = $field->getName();
            if ($this->lastReport->inputKeyExists($name)) {
                $value = $this->lastReport->getRaw($field->getName());
                $ret = $field->isValid($value) && $ret;
            } else {
                $ret = $field->checkRequired(null) && $ret;
            }
        }
        return $ret;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getFiltered($key = null)
    {
        return $this->lastReport->getFiltered($key);
    }

    public function getErrors($key = null)
    {
        return $this->lastReport->getErrors($key);
    }

    public function getRaw($key = null)
    {
        return $this->lastReport->getRaw($key);
    }

    /**
     * Get filtered values for correctly filled data plus raw values for those that contain errors.
     * @param string $key
     * @return array
     */
    public function getMixed($key = null)
    {
        return $this->lastReport->getMixed($key);
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
