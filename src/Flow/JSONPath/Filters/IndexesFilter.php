<?php
namespace Flow\JSONPath\Filters;

class IndexesFilter extends AbstractFilter
{
    /**
     * @param $collection
     * @return array
     */
    public function filter(array $collection)
    {
        $return = [];
        foreach ($this->value as $index) {
            if (isset($data[$index])) {
                $return[] = $data[$index];
            }
        }
        return $return;
    }
}
 