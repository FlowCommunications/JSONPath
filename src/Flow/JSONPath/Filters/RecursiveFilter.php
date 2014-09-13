<?php
namespace Flow\JSONPath\Filters;

class RecursiveFilter extends AbstractFilter
{
    /**
     * @param $collection
     * @return array
     */
    public function filter($collection)
    {
        $result = [];

        $this->recurse($result, $collection);

        return $result;
    }

    private function recurse(& $result, $data)
    {
        if ($this->value == "*") {
            foreach ($data as $v) {
                $result[] = $v;
            }
            foreach ($data as $v) {
                if (is_array($v) || is_object($v)) {
                    $this->recurse($result, (array) $v);
                }
            }
        } else {
            foreach ($data as $v) {
                if (is_array($v) || is_object($v)) {
                    if ($this->keyExists($v, $this->value)) {
                        $result[] = $this->getValue($v, $this->value);
                    }

                    $this->recurse($result, $v);
                }
            }
        }
    }
}
 