<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\AccessHelper;
use Flow\JSONPath\ValueObject;

class QueryMatchFilter extends AbstractFilter
{
    const MATCH_QUERY_OPERATORS = '
    @(\.(?<key>\w+)|\[["\'](?<keySquare>.*?)["\']\])
    (\s*(?<operator>==|=|<>|!==|!=|>|<)\s*(?<comparisonValue>.+))?
    ';

    /**
     * @param array $collection
     * @throws \Exception
     * @return array
     */
    public function filter($collection)
    {
        $return = [];

        $path = @$collection->path();
        $collection = $collection->get();
        
		preg_match('/^' . static::MATCH_QUERY_OPERATORS . '$/x', $this->token->value, $matches);

        if (!isset($matches[1])) {
            throw new \Exception("Malformed filter query");
        }

        $key      = $matches['key'] ?: $matches['keySquare'];

        if ($key === "") {
            throw new \Exception("Malformed filter query: key was not set");
        }

        $operator = isset($matches['operator']) ? $matches['operator'] : null;
        $comparisonValue   = isset($matches['comparisonValue']) ? $matches['comparisonValue'] : null;

        if (strtolower($comparisonValue) === "false") {
            $comparisonValue = false;
        }
        if (strtolower($comparisonValue) === "true") {
            $comparisonValue = true;
        }
        if (strtolower($comparisonValue) === "null") {
            $comparisonValue = null;
        }

        $comparisonValue = preg_replace('/^[\'"]/', '', $comparisonValue);
        $comparisonValue = preg_replace('/[\'"]$/', '', $comparisonValue);

		foreach ($collection as $k => $value) {
            
			if (AccessHelper::keyExists($value, $key, $this->magicIsAllowed)) {
                
                $resultPath = static::path($path, $k);
                
                $value1 = AccessHelper::getValue($value, $key, $this->magicIsAllowed);
				if($value1 instanceof ValueObject) $value1 = $value1->get();

                if ($operator === null && AccessHelper::keyExists($value, $key, $this->magicIsAllowed)) {
					$return[] = new ValueObject($value, $resultPath);
                }

                if (($operator === "=" || $operator === "==") && $value1 == $comparisonValue) {
					$return[] = new ValueObject($value, $resultPath);
                }
                if (($operator === "!=" || $operator === "!==" || $operator === "<>") && $value1 != $comparisonValue) {
					$return[] = new ValueObject($value, $resultPath);
                }
                if ($operator == ">" && $value1 > $comparisonValue) {
					$return[] = new ValueObject($value, $resultPath);
                }
                if ($operator == "<" && $value1 < $comparisonValue) {
					$return[] = new ValueObject($value, $resultPath);
                }
            }
        }

        return $return;
    }
}
 
