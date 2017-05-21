<?php
namespace Flow\JSONPath;

use Iterator;
use ArrayAccess;
use JsonSerializable;
use Countable;

class ValueObject implements Iterator, ArrayAccess, JsonSerializable, Countable
{
	private $value;
	private $path;
    
	public function __construct($value, $path = '')
    {
        if($value instanceof self){
            $this->value = $value->get();
            $this->path = $value->path();
        }else{
            $this->value = $value;
            $this->path = $path;
        }
    }

    public function offsetExists($offset)
    {
        return AccessHelper::keyExists($this->value, $offset);
    }

    public function offsetGet($offset)
    {
        return AccessHelper::getValue($this->value, $offset);
    }
    
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->value[] = $value;
        } else {
            AccessHelper::setValue($this->value, $offset, $value);
        }
    }

    public function offsetUnset($offset)
    {
        AccessHelper::unsetValue($this->value, $offset);
    }
    
	public function path()
    {
        return $this->path;    
	}
	
	public function get(){
		return $this->value;
	}

    public function __get($key)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : null;
    }
    
	public function __set($key, $value)
    {
		AccessHelper::setValue($this->value, $key, $value);
	}

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current(){
        $value = current($this->value);
        return AccessHelper::isCollectionType($value) ? new static($value, $this->path()) : $value;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next(){
        next($this->value);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key(){
        return key($this->value);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid(){
        return key($this->value) !== null;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind(){
        reset($this->value);
    }
    
    public function jsonSerialize()
    {
        return $this->value;
    }
    
    public function count()
    {
        return count($this->value);
    }
}
