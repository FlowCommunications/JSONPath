<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;

abstract class AbstractFilter
{
    protected $magicIsAllowed;

    public function __construct($value, $options = 0)
    {
        $this->value = $value;
        $this->options = $options;
        $this->magicIsAllowed = $this->options & JSONPath::ALLOW_MAGIC;
    }

    public function isMagicAllowed()
    {
        return $this->magicIsAllowed;
    }

    /**
     * @param $collection
     * @return array
     */
    abstract public function filter($collection);
}
 