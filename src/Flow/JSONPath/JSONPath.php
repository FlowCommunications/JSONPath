<?php
namespace Flow\JSONPath;


use Flow\JsonPath\Filters\AbstractFilter;

class JSONPath
{
    protected static $tokenCache = [];

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Evaluate an expression
     * @param $expression
     * @return array
     * @throws \Exception
     */
    public function find($expression)
    {
        $tokens = $this->parseTokens($expression);

        $collectionData = [$this->data];

        while (count($tokens)) {
            $token = array_shift($tokens);

            $filter = $this->buildFilter($token);

            $filteredData = [];

            foreach ($collectionData as $value) {
                $filteredData = array_merge($filteredData, $filter->filter($value));
            }

            $collectionData = $filteredData;
        }

        return $collectionData;
    }

    /**
     * Evaluate an expression and return the first result
     * @param $expression
     * @return array|null
     */
    public function first($expression)
    {
        $result = $this->find($expression);
        return array_key_exists(0, $result) ? $result[0] : null;
    }

    /**
     * Evaluate an expression and return the last result
     * @param $expression
     * @return mixed
     */
    public function last($expression)
    {
        $result = $this->find($expression);
        $length = count($result);
        return array_key_exists($length - 1, $result) ? $result[$length - 1] : null;
    }

    /**
     * @param $token
     * @return AbstractFilter
     * @throws \Exception
     */
    public function buildFilter($token)
    {
        $filterClass = 'Flow\\JSONPath\\Filters\\' . ucfirst($token['type']) . 'Filter';

        if (! class_exists($filterClass)) {
            throw new JSONPathException("No filter class exists for token [{$token['type']}]");
        }

        return new $filterClass($token['value']);
    }

    /**
     * @param $expression
     * @return array
     * @throws \Exception
     */
    public function parseTokens($expression)
    {
        $cacheKey = md5($expression);

        if (isset(static::$tokenCache[$cacheKey])) {
            return static::$tokenCache[$cacheKey];
        }

        $expression = trim($expression);
        $expression = preg_replace('/^\$/', '', $expression);

        $lexer = new JSONPathLexer($expression);

        $tokens = $lexer->parseExpression();

        static::$tokenCache[$cacheKey] = $tokens;

        return $tokens;
    }

}
