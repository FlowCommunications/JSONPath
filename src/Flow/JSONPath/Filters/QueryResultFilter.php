<?php
namespace Flow\JSONPath\Filters;

class QueryResultFilter extends AbstractFilter
{

    /**
     * @param array $collection
     * @throws \Exception
     * @return array
     */
    public function filter(array $collection)
    {
        $result = [];

        preg_match('/@\.(?<key>\w+)\s*(?<operator>-|\+|\*|\/)\s*(?<numeric>\d+)/', $this->value, $matches);

        if (array_key_exists($matches['key'], $collection)) {
            $value = $collection[$matches['key']];
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
                throw new \Exception("Unsupported operator in expression");
                break;
        }

        if (array_key_exists($resultKey, $collection)) {
            $result[] = $collection[$resultKey];
        }

        return $result;
    }
}
 