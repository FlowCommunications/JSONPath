<?php
namespace Flow\JSONPath;

use ArrayAccess;
use Iterator;
use JsonSerializable;
use Flow\JsonPath\Filters\AbstractFilter;

class JSONPath implements ArrayAccess, Iterator, JsonSerializable
{
    protected static $tokenCache = [];

    protected $data;
    protected $options;

    const ALLOW_MAGIC = 1;

    public function __construct($data, $options = 0)
    {
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Evaluate an expression
     * @param $expression
     * @return array
     */
    public function find($expression)
    {
        $tokens = $this->parseTokens($expression);

        $collectionData = [$this->data];

        while (count($tokens)) {
            $token = array_shift($tokens);

            $filter = $this->buildFilter($token);

            $filteredData = [];

            foreach ($collectionData as $value) {
                if (AccessHelper::isCollectionType($value)) {
                    $filteredData = array_merge($filteredData, $filter->filter($value));
                }
            }

            $collectionData = $filteredData;
        }


        return new static($collectionData, $this->options);
    }

    /**
     * @return mixed
     */
    public function first()
    {
        $keys = AccessHelper::collectionKeys($this->data);
        return $this->data[$keys[0]] ? $this->data[$keys[0]] : null;
    }

    /**
     * Evaluate an expression and return the last result
     * @return mixed
     */
    public function last()
    {
        $keys = AccessHelper::collectionKeys($this->data);
        return $this->data[end($keys)] ? $this->data[end($keys)] : null;
    }

    /**
     * @param $token
     * @return AbstractFilter
     * @throws \Exception
     */
    public function buildFilter($token)
    {
        $filterClass = 'Flow\\JSONPath\\Filters\\' . ucfirst($token['type']) . 'Filter';

        if (! class_exists($filterClass)) {
            throw new JSONPathException("No filter class exists for token [{$token['type']}]");
        }

        return new $filterClass($token['value'], $this->options);
    }

    /**
     * @param $expression
     * @return array
     * @throws \Exception
     */
    public function parseTokens($expression)
    {
        $cacheKey = md5($expression);

        if (isset(static::$tokenCache[$cacheKey])) {
            return static::$tokenCache[$cacheKey];
        }

        $expression = trim($expression);
        $expression = preg_replace('/^\$/', '', $expression);

        $lexer = new JSONPathLexer($expression);

        $tokens = $lexer->parseExpression();

        static::$tokenCache[$cacheKey] = $tokens;

        return $tokens;
    }

    public function data()
    {
        return $this->data;
    }

    public function offsetExists($offset)
    {
        return AccessHelper::keyExists($this->data, $offset);
    }

    public function offsetGet($offset)
    {
        $value = AccessHelper::getValue($this->data, $offset);

        return AccessHelper::isCollectionType($value) ? new static($value, $this->options) : $value;
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            AccessHelper::setValue($this->data, $offset, $value);
        }
    }

    public function offsetUnset($offset)
    {
        AccessHelper::unsetValue($this->data, $offset);
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function __get($key)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : null;
    }

    /**
     * Return the current element
     */
    public function current()
    {
        $value = current($this->data);

        return AccessHelper::isCollectionType($value) ? new static($value, $this->options) : $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * Return the key of the current element
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Checks if current position is valid
     */
    public function valid()
    {
        return key($this->data) !== null;
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        reset($this->data);
    }
}
