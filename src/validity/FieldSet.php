<?php

namespace validity;

class FieldSet
{
    /** @var Field[] */
    private $fields = array();
    /** @var Report */
    private $lastReport;

    /**
     * @param Field $Field
     * @return FieldSet
     */
    public function add(Field $Field): FieldSet
    {
        $this->fields[] = $Field;
        return $this;
    }

    /**
     * @return Report
     */
    public function lastReport(): Report
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
        foreach ($this->fields as $Field) {
            $Field->isValid($this->lastReport);
        }
        return $this->lastReport->isOk();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getFiltered($key = null)
    {
        return $this->lastReport()->getFiltered($key);
    }

    public function getErrors($key = null)
    {
        return $this->lastReport()->getErrors($key);
    }

    public function getRaw($key = null)
    {
        return $this->lastReport()->getRaw($key);
    }
}
