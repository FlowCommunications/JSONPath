<?php
namespace Flow\JSONPath;

use ArrayAccess;
use Iterator;
use JsonSerializable;
use Flow\JsonPath\Filters\AbstractFilter;
use Countable;

class JSONPath implements ArrayAccess, Iterator, JsonSerializable, Countable
{
    protected static $tokenCache = [];

    protected $data;

    protected $options;

    const ALLOW_MAGIC = 1;

    /**
     * @param $data
     * @param int $options
     */
    public function __construct($data, $options = 0)
    {
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Evaluate an expression
     *
     * @param $expression
     * @return static
     * @throws JSONPathException
     */
    public function find($expression)
    {
        $this->data=$this->data instanceof ValueObject ? new ValueObject(self::unwrap($this->data), '$'):new ValueObject($this->data, '$');
        $tokens = $this->parseTokens($expression);
		$collectionData = [ $this->data ];
        foreach ($tokens as $token) {
            $filter = $token->buildFilter($this->options);

            $filteredData = [];

            foreach ($collectionData as $value) {

                if (AccessHelper::isCollectionType($value)) {
                    $filteredValue = $filter->filter($value);
                    $filteredData = array_merge($filteredData, $filteredValue);
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

        if (empty($keys)) {
            return null;
        }

        $value = isset($this->data[$keys[0]]) ? $this->data[$keys[0]] : null;

        return AccessHelper::isCollectionType($value) ? new static($value, $this->options) : $value;
    }

    /**
     * Evaluate an expression and return the last result
     * @return mixed
     */
    public function last()
    {
        $keys = AccessHelper::collectionKeys($this->data);

        if (empty($keys)) {
            return null;
        }

        $value = $this->data[end($keys)] ? $this->data[end($keys)] : null;

        return AccessHelper::isCollectionType($value) ? new static($value, $this->options) : $value;
    }

    /**
     * Evaluate an expression and return the first key
     * @return mixed
     */
    public function firstKey()
    {
        $keys = AccessHelper::collectionKeys($this->data);

        if (empty($keys)) {
            return null;
        }

        return $keys[0];
    }

    /**
     * Evaluate an expression and return the last key
     * @return mixed
     */
    public function lastKey()
    {
        $keys = AccessHelper::collectionKeys($this->data);

        if (empty($keys) || end($keys) === false) {
            return null;
        }

        return end($keys);
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

        $lexer = new JSONPathLexer($expression);

        $tokens = $lexer->parseExpression();

        static::$tokenCache[$cacheKey] = $tokens;

        return $tokens;
    }

    /**
     * @return mixed
     */
    public function data()
    {
		return self::unwrap($this->data);
    }

    private static function unwrap($data, $rec=true){
        $data = $data instanceof ValueObject ? $data->get() : $data;
        if(AccessHelper::isCollectionType($data) && $rec){
            foreach ($data as $key=>$value){
                AccessHelper::setValue($data, $key, self::unwrap($value, $rec));
            }
        }
        return $data;
    }

    /**
     * @return mixed
     */
    public function dataWithPath()
    {
        return $this->data;
    }
    
	public function paths()
    {
		$data = $this->data instanceof ValueObject ? $this->data->get(): $this->data;
		if(empty($data)) return new static([], $this->options);
		return new static(array_map(function($each){ return @$each->path(); }, $data), $this->options);
    }

    public function offsetExists($offset)
    {
        return AccessHelper::keyExists($this->data, $offset);
    }

    public function offsetGet($offset)
    {
        $value = AccessHelper::getValue($this->data, $offset);

        return AccessHelper::isCollectionType($value)
            ? new static($value, $this->options)
			: self::unwrap($value, false);
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

    /**
     * Return the current element
     */
    public function current()
    {
        $value = current($this->data);

        return AccessHelper::isCollectionType($value) ? new static($value, $this->options) : $value;
    }

    /**
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
    
    public function count()
    {
        return count($this->data);
    }

    /**
     * @param $key
     * @return JSONPath|mixed|null|static
     */
    public function __get($key)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : null;
    }
}
