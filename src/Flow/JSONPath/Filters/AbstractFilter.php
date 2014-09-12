<?php
namespace Flow\JSONPath\Filters;

abstract class AbstractFilter
{
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param $collection
     * @return array
     */
    abstract public function filter(array $collection);
}
 