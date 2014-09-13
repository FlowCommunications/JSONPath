<?php
namespace Flow\JSONPath\Filters;

class IndexFilter extends AbstractFilter
{
    /**
     * @param array $collection
     * @return array
     */
    public function filter($collection)
    {
        $return = [];

        if ($this->keyExists($collection, $this->value)) {
            $return[] = $this->getValue($collection, $this->value);
        } else if ($this->value === "*") {
            return $this->arrayValues($collection);
        }

        return $return;
    }

}
 