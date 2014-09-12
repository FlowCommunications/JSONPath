<?php

namespace Flow\JSONPath;

class JSONPathLexer
{
    const T_NONE         = 'none';
    const T_INDEX        = 'index';
    const T_RECURSIVE    = 'recursive';
    const T_QUERY_RESULT = 'queryResult';
    const T_QUERY_MATCH  = 'queryMatch';
    const T_SLICE        = 'slice';
    const T_INDEXES      = 'indexes';

    const GROUP_RECURSIVE_INDEX = '\.\.(?:\w+|\*)';
    const GROUP_BRACKET         = '\[.+?\]';
    const GROUP_DOT_INDEX       = '\.\w+';
    const GROUP_DOT_WILDCARD    = '\.\*';

    const MATCH_INDEX        = '\w+|\*';
    const MATCH_INDEXES      = '\d+[,\d]+';
    const MATCH_SLICE        = '[-\d:]+';
    const MATCH_QUERY_RESULT = '\(.+?\)';
    const MATCH_QUERY_MATCH  = '\?\(.+?\)';
    const MATCH_INDEX_ALT    = '["\']?(.+?)["\']?';

    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    public function parseExpression()
    {
        $tokens = [];

        $regex = '/(' . implode(')|(', $this->getCatchablePatterns()) . ')/i';

        $flags   = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $matches = preg_split($regex, $this->expression, -1, $flags);

        foreach ($matches as $match) {
            // Must remain before 'value' assignment since it can change content
            $type = $this->getType($match[0]);

            if ($type === JSONPathLexer::T_NONE) {
                throw new JSONPathException("Unexpected token {$match[0]} at position {$match[1]} of expression: $this->expression");
            }

            $tokens[] = array(
                'value'    => $match[0],
                'type'     => $type,
                'position' => $match[1],
            );
        }

        return $tokens;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCatchablePatterns()
    {
        return array(
            self::GROUP_BRACKET,
            self::GROUP_RECURSIVE_INDEX,
            self::GROUP_DOT_INDEX,
            self::GROUP_DOT_WILDCARD,
        );
    }

    /**
     * @param $value
     * @return string
     */
    protected function getType(&$value)
    {
        if (preg_match('/^' . static::GROUP_DOT_WILDCARD . '$/', $value)) {
            $value = '*';
            return self::T_INDEX;
        }

        if (preg_match('/^' . static::GROUP_DOT_INDEX . '$/', $value)) {
            $value = substr($value, 1);
            return self::T_INDEX;
        }

        if (preg_match('/^' . static::GROUP_RECURSIVE_INDEX . '$/', $value)) {
            $value = substr($value, 2);
            return self::T_RECURSIVE;
        }

        if (preg_match('/^' . static::GROUP_BRACKET . '$/', $value)) {
            $value = substr($value, 1, -1);

            if (preg_match('/^(' . static::MATCH_INDEX . ')$/', $value, $bracketMatches)) {
                return self::T_INDEX;
            }

            if (preg_match('/^' . static::MATCH_INDEXES . '$/', $value, $bracketMatches)) {
                $value = explode(',', $value);
                return self::T_INDEXES;
            }

            if (preg_match('/^' . static::MATCH_SLICE . '$/', $value, $bracketMatches)) {
                $parts = explode(':', $value);

                $value = [
                    'start' => isset($parts[0]) && $parts[0] !== "" ? (int) $parts[0] : null,
                    'end'   => isset($parts[1]) && $parts[1] !== "" ? (int) $parts[1] : null,
                    'step'  => isset($parts[2]) && $parts[2] !== "" ? (int) $parts[2] : null,
                ];

                return self::T_SLICE;
            }

            if (preg_match('/^' . static::MATCH_QUERY_RESULT . '$/', $value)) {
                $value = substr($value, 1, -1);

                return self::T_QUERY_RESULT;
            }

            if (preg_match('/^' . static::MATCH_QUERY_MATCH . '$/', $value)) {
                $value = substr($value, 2, -1);

                return self::T_QUERY_MATCH;
            }

            if (preg_match('/^' . static::MATCH_INDEX_ALT . '$/', $value, $bracketMatches)) {
                $value = $bracketMatches[1];

                return self::T_INDEX;
            }

        }

        return self::T_NONE;
    }

}