<?php
namespace Flow\JSONPath\Filters;

class RecursiveFilter extends AbstractFilter
{
    /**
     * @param $collection
     * @return array
     */
    public function filter(array $collection)
    {
        $result = [];

        $this->recurse($result, $collection);

        return $result;
    }

    private function recurse(& $result, $data)
    {
        if ($this->value == "*") {
            foreach ($data as $k => $v) {
                $result[] = $v;
            }
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $this->recurse($result, $v);
                }
            }

        } else {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    if (array_key_exists($this->value, $v)) {
                        $result[] = $v[$this->value];
                    }
                    $this->recurse($result, $v);
                }
            }
        }
    }
}
 