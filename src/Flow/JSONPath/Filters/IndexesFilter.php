<?php
namespace Flow\JSONPath\Filters;

class IndexesFilter extends AbstractFilter
{
    /**
     * @param $collection
     * @return array
     */
    public function filter($collection)
    {
        $return = [];
        foreach ($this->value as $index) {
            if ($this->keyExists($collection, $index)) {
                $return[] = $this->getValue($collection, $index);
            }
        }
        return $return;
    }
}
 