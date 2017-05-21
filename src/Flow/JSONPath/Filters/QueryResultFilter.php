<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\AccessHelper;
use Flow\JSONPath\JSONPathException;
use Flow\JSONPath\ValueObject;

class QueryResultFilter extends AbstractFilter
{

    /**
     * @param array $collection
     * @return array
     */
    public function filter($collection)
    {
        $result = [];
        $path = @$collection->path();
        $collection = $collection->get();
        preg_match('/@\.(?<key>\w+)\s*(?<operator>-|\+|\*|\/)\s*(?<numeric>\d+)/', $this->token->value, $matches);

        $matchKey = $matches['key'];
        
        if (AccessHelper::keyExists($collection, $matchKey, $this->magicIsAllowed)) {
			$value = AccessHelper::getValue($collection, $matchKey, $this->magicIsAllowed);
		} else {
            if ($matches['key'] === 'length') {
                $value = count($collection);
            } else {
                return;
            }
        }

        switch ($matches['operator']) {
            case '+':
                $resultKey = $value + $matches['numeric'];
                break;
            case '*':
                $resultKey = $value * $matches['numeric'];
                break;
            case '-':
                $resultKey = $value - $matches['numeric'];
                break;
            case '/':
                $resultKey = $value / $matches['numeric'];
                break;
            default:
                throw new JSONPathException("Unsupported operator in expression");
                break;
        }

        if (AccessHelper::keyExists($collection, $resultKey, $this->magicIsAllowed)) {
            $result[] = new ValueObject(AccessHelper::getValue($collection, $resultKey, $this->magicIsAllowed), static::path($path, $resultKey));
        }

        return $result;
    }
}
 
