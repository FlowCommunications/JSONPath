<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\AccessHelper;

class IndexFilter extends AbstractFilter
{
    /**
     * @param array $collection
     * @return array
     */
    public function &filter(& $collection)
    {
        $arr = [];

        if (AccessHelper::keyExists($collection, $this->token->value, $this->magicIsAllowed)) {
            $arr[] =& AccessHelper::getValue($collection, $this->token->value, $this->magicIsAllowed);
        } else {
            if ($this->token->value === "*") {
                $arr =& AccessHelper::arrayValues($collection);
            }
        }

        return $arr;
    }

}
 