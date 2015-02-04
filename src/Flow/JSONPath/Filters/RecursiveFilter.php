<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\AccessHelper;

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
                    if (AccessHelper::keyExists($v, $this->value, $this->magicIsAllowed)) {
                        $result[] = AccessHelper::getValue($v, $this->value, $this->magicIsAllowed);
                    }

                    $this->recurse($result, $v);
                }
            }
        }
    }
}
 