<?php

namespace Flow\JSONPath;

class JSONPathLexer
{
    /*
     * Tokens
     */
    const T_NONE         = 'none';
    const T_INDEX        = 'index';
    const T_RECURSIVE    = 'recursive';
    const T_QUERY_RESULT = 'queryResult';
    const T_QUERY_MATCH  = 'queryMatch';
    const T_SLICE        = 'slice';
    const T_INDEXES      = 'indexes';

    /*
     * Major groups
     */
    const GROUP_RECURSIVE_INDEX = '\.\.(?:\w+|\*)';
    const GROUP_BRACKET         = '\[.+?\]';
    const GROUP_DOT_INDEX       = '\.(?:\w+|\*)';

    /*
     * Match within bracket groups
     * Matches are whitespace insensitive
     */
    const MATCH_INDEX        = '\w+ | \*';
    const MATCH_INDEXES      = '\s* \d+ [\d,\s]+';
    const MATCH_SLICE        = '[-\d:]+ | :';
    const MATCH_QUERY_RESULT = '\s* \(.+?\) \s*';
    const MATCH_QUERY_MATCH  = '\s* \?\(.+?\) \s*';
    const MATCH_INDEX_ALT    = '\s* ["\']? (.+?) ["\']? \s*';

    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    public function parseExpressionGroups()
    {
        $squareBracketDepth = 0;
        $capturing = false;
        $group = '';
        $groups = [];
        $token = array(
            'value' => '',
            'type' => '',
        );

        for ($i = 0; $i < strlen($this->expression); $i += 1) {
            $char = $this->expression[$i];

            if ($char === '.' && $squareBracketDepth === 0) {
                if (! empty($group) && $group !== '.') {
                    $groups[] = $group;
                    $group = '';
                }
                $capturing = true;
            }

            if ($char == "[") {
                if ($squareBracketDepth === 0) {
                    if (! empty($group)) {
                        $groups[] = $group;
                        $group = '';
                    }
                }

                $capturing = true;
                $squareBracketDepth += 1;
            }

            if ($capturing) {
                $group .= $this->expression[$i];
            }


            if ($char == "]") {
                $squareBracketDepth -= 1;

                if ($squareBracketDepth == 0) {
                    $capturing = false;
                    $groups[] = $group;
                    $group = '';
                }
            }

        }

        if (! empty($group)) {
            $groups[] = $group;
        }

        return $groups;
    }

    protected function lookAhead($pos, $forward = 1)
    {
        return isset($this->expression[$pos + $forward]) ? $this->expression[$pos + $forward] : null;
    }



    public function parseExpression()
    {
        $tokens = [];

        $groups = $this->parseExpressionGroups();

        foreach ($groups as $group) {
            // Must remain before 'value' assignment since it can change content
            $type = $this->getType($group);

            if ($type === JSONPathLexer::T_NONE) {
                throw new JSONPathException("Unable to parse token {$group} in expression: $this->expression");
            }

            $tokens[] = array(
                'value'    => $group,
                'type'     => $type,
            );
        }

        return $tokens;
    }

    /**
     * @param $value
     * @return string
     */
    protected function getType(&$value)
    {
        if (preg_match('/^' . static::GROUP_DOT_INDEX . '$/x', $value)) {
            $value = substr($value, 1);
            return self::T_INDEX;
        }

        if (preg_match('/^' . static::GROUP_RECURSIVE_INDEX . '$/x', $value)) {
            $value = substr($value, 2);
            return self::T_RECURSIVE;
        }

        if (preg_match('/^' . static::GROUP_BRACKET . '$/x', $value)) {
            $value = substr($value, 1, -1);

            if (preg_match('/^(' . static::MATCH_INDEX . ')$/x', $value, $bracketMatches)) {
                return self::T_INDEX;
            }

            if (preg_match('/^' . static::MATCH_INDEXES . '$/x', $value, $bracketMatches)) {
                $value = explode(',', $value);
                foreach ($value as & $v) {
                    $v = (int) trim($v);
                }
                return self::T_INDEXES;
            }

            if (preg_match('/^' . static::MATCH_SLICE . '$/x', $value, $bracketMatches)) {
                $parts = explode(':', $value);

                $value = [
                    'start' => isset($parts[0]) && $parts[0] !== "" ? (int) $parts[0] : null,
                    'end'   => isset($parts[1]) && $parts[1] !== "" ? (int) $parts[1] : null,
                    'step'  => isset($parts[2]) && $parts[2] !== "" ? (int) $parts[2] : null,
                ];

                return self::T_SLICE;
            }

            if (preg_match('/^' . static::MATCH_QUERY_RESULT . '$/x', $value)) {
                $value = substr($value, 1, -1);

                return self::T_QUERY_RESULT;
            }

            if (preg_match('/^' . static::MATCH_QUERY_MATCH . '$/x', $value)) {
                $value = substr($value, 2, -1);

                return self::T_QUERY_MATCH;
            }

            if (preg_match('/^' . static::MATCH_INDEX_ALT . '$/x', $value, $bracketMatches)) {
                $value = $bracketMatches[1];
                $value = trim($value);

                return self::T_INDEX;
            }

        }

        return self::T_NONE;
    }

}