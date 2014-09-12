<?php
namespace Flow\JSONPath\Filters;

class IndexFilter extends AbstractFilter
{
    /**
     * @param array $collection
     * @return array
     */
    public function filter(array $collection)
    {
        $return = [];

        if (array_key_exists($this->value, $collection)) {
            $return[] = $collection[$this->value];
        } else if ($this->value === "*") {
            return array_values($collection);
        }

        return $return;
    }

}
 