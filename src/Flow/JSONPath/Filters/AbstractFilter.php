<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;

abstract class AbstractFilter
{
    public function __construct($value, $options = 0)
    {
        $this->value = $value;
        $this->options = $options;
    }

    public function keyExists($collection, $key)
    {
        if ($this->isMagicAllowed() && is_object($collection) && method_exists($collection, '__get')) {
            return true;
        }

        if (is_array($collection) || $collection instanceof \ArrayAccess) {
            return array_key_exists($key, $collection);
        } else if (is_object($collection)) {
            return property_exists($collection, $key);
        }
    }

    public function getValue($collection, $key)
    {
        if ($this->isMagicAllowed() && is_object($collection) && method_exists($collection, '__get')) {
            return $collection->__get($key);
        }

        if (is_array($collection) || $collection instanceof \ArrayAccess) {
            return $collection[$key];
        } else if (is_object($collection)) {
            return $collection->$key;
        }
    }

    public function arrayValues($collection)
    {
        if (is_array($collection)) {
            return array_values($collection);
        } else if (is_object($collection)) {
            return array_values((array) $collection);
        }

        throw new JSONPathException("Invalid variable type for arrayValues");
    }

    public function isMagicAllowed()
    {
        return $this->options & JSONPath::ALLOW_MAGIC;
    }

    /**
     * @param $collection
     * @return array
     */
    abstract public function filter($collection);
}
 