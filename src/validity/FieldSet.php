<?php

namespace validity;

class FieldSet
{
    /** @var Field[] */
    private $Fields = array();
    /** @var Result */
    private $LastResult;

    /**
     * @param Field $Field
     * @return FieldSet
     */
    public function add(Field $Field)
    {
        $this->Fields[] = $Field;
        return $this;
    }

    /**
     * @return Result
     */
    public function lastResult()
    {
        return $this->LastResult;
    }

    /**
     * @param $data
     * @return Result
     */
    public function isValid($data)
    {
        $this->LastResult = new Result($data);
        foreach ($this->Fields as $Field) {
            $Field->isValid($this->LastResult);
        }
        return $this->LastResult->isOk();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getFiltered($key = null)
    {
        return $this->lastResult()->getFiltered($key);
    }

    public function getErrors($key = null)
    {
        return $this->lastResult()->getErrors($key);
    }

    public function getRaw($key = null)
    {
        return $this->lastResult()->getRaw($key);
    }
}
