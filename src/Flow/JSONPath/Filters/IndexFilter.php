<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\AccessHelper;
use Flow\JSONPath\ValueObject;

class IndexFilter extends AbstractFilter
{
    /**
     * @param array $collection
     * @return array
     */
    public function filter($collection)
    {
        if (AccessHelper::keyExists($collection, $this->token->value, $this->magicIsAllowed)) {
            $v = AccessHelper::getValue($collection, $this->token->value, $this->magicIsAllowed);
            return [
				new ValueObject($v, $collection->path().'.'.$this->token->value)
            ];
        } else if ($this->token->value === "*") {
            return array_map(function($value, $key) use ($collection){ return new ValueObject($value, $collection->path().'.'.$key); }, AccessHelper::arrayValues($collection), AccessHelper::arrayKeys($collection));
        }

        return [];
    }

}
 
