<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\AccessHelper;

class QueryMatchFilter extends AbstractFilter
{
    const MATCH_QUERY_OPERATORS = '
    @(\.(?<key>\w+)|\[["\'](?<keySquare>.*?)["\']\])
    (\s*(?<operator>==|=|>|<|in)\s*(?<comparisonValue>\S.+))?
    ';

    /**
     * @param array $collection
     * @throws \Exception
     * @return array
     */
    public function filter($collection)
    {
        $return = [];

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

        if (substr($comparisonValue, 0, 1) === "[" && substr($comparisonValue, -1) === "]") {
            $comparisonValue = substr($comparisonValue, 1, -1);
            $comparisonValue = preg_replace('/^[\'"]/', '', $comparisonValue);
            $comparisonValue = preg_replace('/[\'"]$/', '', $comparisonValue);
            $comparisonValue = preg_replace('/[\'"],[ ]{0,}[\'"]/', ',', $comparisonValue);

            $comparisonValue = explode(",", $comparisonValue);
        } else {
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
        }

        foreach ($collection as $value) {
            if (AccessHelper::keyExists($value, $key, $this->magicIsAllowed)) {
                $value1 = AccessHelper::getValue($value, $key, $this->magicIsAllowed);

                if ($operator === null && AccessHelper::keyExists($value, $key, $this->magicIsAllowed)) {
                    $return[] = $value;
                }

                if (($operator === "=" || $operator === "==") && $value1 == $comparisonValue) {
                    $return[] = $value;
                }
                if (($operator === "!=" || $operator === "!==") && $value1 != $comparisonValue) {
                    $return[] = $value;
                }
                if ($operator == ">" && $value1 > $comparisonValue) {
                    $return[] = $value;
                }
                if ($operator == "<" && $value1 < $comparisonValue) {
                    $return[] = $value;
                }
                if ($operator == "in" && in_array($value1, $comparisonValue)) {
                    $return[] = $value;
                }
            }
        }

        return $return;
    }
}
 
