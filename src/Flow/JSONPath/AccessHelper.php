<?php
namespace Flow\JSONPath;

class AccessHelper
{
    public static function collectionKeys($collection)
    {
		if($collection instanceof ValueObject) $collection = $collection->get();

        if (is_object($collection)) {
            return array_keys(get_object_vars($collection));
        } else {
            return array_keys($collection);
        }
    }

    public static function isCollectionType($collection)
    {
		if($collection instanceof ValueObject) $collection = $collection->get();

        return is_array($collection) || is_object($collection);
    }

    public static function keyExists($collection, $key, $magicIsAllowed = false)
    {
		if($collection instanceof ValueObject) $collection = $collection->get();

        if ($magicIsAllowed && is_object($collection) && method_exists($collection, '__get')) {
            return true;
        }
        if (is_array($collection) || $collection instanceof \ArrayAccess) {
            return array_key_exists($key, $collection);
        } else if (is_object($collection)) {
            return property_exists($collection, $key);
        }
    }

    public static function getValue($collection, $key, $magicIsAllowed = false)
    {
		if($collection instanceof ValueObject) $collection = $collection->get();
        
		if ($magicIsAllowed && is_object($collection) && method_exists($collection, '__get')) {
            return $collection->__get($key);
        }

        if (is_object($collection) && ! $collection instanceof \ArrayAccess) {
            return $collection->$key;
        } else {
            return $collection[$key];
        }
    }

    public static function setValue(&$collection, $key, $value)
    {
        if (is_object($collection) && ! $collection instanceof \ArrayAccess) {
            return $collection->$key = $value;
        } else {
            return $collection[$key] = $value;
        }
    }

    public static function unsetValue(&$collection, $key)
    {
        if (is_object($collection) && ! $collection instanceof \ArrayAccess) {
            unset($collection->$key);
        } else {
            unset($collection[$key]);
        }
    }

    public static function arrayValues($collection)
    {
		if($collection instanceof ValueObject) $collection = $collection->get();
        
		if (is_array($collection)) {
            return array_values($collection);
        } else if (is_object($collection)) {
            return array_values((array) $collection);
        }

        throw new JSONPathException("Invalid variable type for arrayValues");
    }
	
	public static function arrayKeys($collection)
    {
		if($collection instanceof ValueObject) $collection = $collection->get();
        
		if (is_array($collection)) {
            return array_keys($collection);
        } else if (is_object($collection)) {
            return array_keys((array) $collection);
        }

        throw new JSONPathException("Invalid variable type for arrayValues");
    }
}
