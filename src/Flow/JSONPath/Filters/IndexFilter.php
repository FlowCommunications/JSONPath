<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\AccessHelper;

class IndexFilter extends AbstractFilter
{
    /**
     * @param array $collection
     * @return array
     */
    public function filter($collection)
    {
        $return = [];

        if (AccessHelper::keyExists($collection, $this->value, $this->magicIsAllowed)) {
            $return[] = AccessHelper::getValue($collection, $this->value, $this->magicIsAllowed);
        } else if ($this->value === "*") {
            return AccessHelper::arrayValues($collection);
        }

        return $return;
    }

}
 