<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\AccessHelper;
use Flow\JSONPath\ValueObject;

class IndexesFilter extends AbstractFilter
{
    /**
     * @param $collection
     * @return array
     */
    public function filter($collection)
    {
        $return = [];
        foreach ($this->token->value as $index) {
            if (AccessHelper::keyExists($collection, $index, $this->magicIsAllowed)) {
				$return[] = new ValueObject(AccessHelper::getValue($collection, $index, $this->magicIsAllowed), 
                    static::path($collection->path(), $index));
            }
        }
        return $return;
    }
}
 
