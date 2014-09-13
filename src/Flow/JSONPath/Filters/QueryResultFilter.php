<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\JSONPathException;

class QueryResultFilter extends AbstractFilter
{

    /**
     * @param array $collection
     * @return array
     */
    public function filter($collection)
    {
        $result = [];

        preg_match('/@\.(?<key>\w+)\s*(?<operator>-|\+|\*|\/)\s*(?<numeric>\d+)/', $this->value, $matches);

        $matchKey = $matches['key'];

        if ($this->keyExists($collection, $matchKey)) {
            $value = $this->getValue($collection, $matchKey);
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

        if ($this->keyExists($collection, $resultKey)) {
            $result[] = $this->getValue($collection, $resultKey);
        }

        return $result;
    }
}
 