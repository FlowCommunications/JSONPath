<?php
namespace Flow\JSONPath;

require_once __DIR__ . "/../vendor/autoload.php";

class JSONPathLexerTest extends \PHPUnit_Framework_TestCase
{
    public function test_Index_Wildcard()
    {
        $tokens = (new JSONPathLexer('.*'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_INDEX, $tokens[0]['type']);
        $this->assertEquals("*", $tokens[0]['value']);
    }

    /**
     * @expectedException           Flow\JSONPath\JSONPathException
     * @expectedExceptionMessage    Unexpected token * at position 6 of expression: .hello*
     */
    public function test_Index_BadlyFormed()
    {
        $tokens = (new JSONPathLexer('.hello*'))->parseExpression();
    }

    public function test_Index_Integer()
    {
        $tokens = (new \Flow\JSONPath\JSONPathLexer('[0]'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_INDEX, $tokens[0]['type']);
        $this->assertEquals("0", $tokens[0]['value']);
    }

    public function test_Index_Word()
    {
        $tokens = (new \Flow\JSONPath\JSONPathLexer('["foo$-/\'"]'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_INDEX, $tokens[0]['type']);
        $this->assertEquals("foo$-/'", $tokens[0]['value']);
    }

    public function test_Index_WordWithWhitespace()
    {
        $tokens = (new \Flow\JSONPath\JSONPathLexer('[   "foo$-/\'"     ]'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_INDEX, $tokens[0]['type']);
        $this->assertEquals("foo$-/'", $tokens[0]['value']);
    }

    public function test_Slice_Simple()
    {
        $tokens = (new \Flow\JSONPath\JSONPathLexer('[0:1:2]'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_SLICE, $tokens[0]['type']);
        $this->assertEquals(['start' => 0, 'end' => 1, 'step' => 2], $tokens[0]['value']);
    }

    public function test_Slice_NegativeIndex()
    {
        $tokens = (new \Flow\JSONPath\JSONPathLexer('[-1]'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_SLICE, $tokens[0]['type']);
        $this->assertEquals(['start' => -1, 'end' => null, 'step' => null], $tokens[0]['value']);
    }

    public function test_Slice_AllNull()
    {
        $tokens = (new \Flow\JSONPath\JSONPathLexer('[:]'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_SLICE, $tokens[0]['type']);
        $this->assertEquals(['start' => null, 'end' => null, 'step' => null], $tokens[0]['value']);
    }

    public function test_QueryResult_Simple()
    {
        $tokens = (new \Flow\JSONPath\JSONPathLexer('[(@.foo + 2)]'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_QUERY_RESULT, $tokens[0]['type']);
        $this->assertEquals('@.foo + 2', $tokens[0]['value']);
    }

    public function test_QueryMatch_Simple()
    {
        $tokens = (new \Flow\JSONPath\JSONPathLexer('[?(@.foo < \'bar\')]'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_QUERY_MATCH, $tokens[0]['type']);
        $this->assertEquals('@.foo < \'bar\'', $tokens[0]['value']);
    }


    public function test_Recursive_Simple()
    {
        $tokens = (new \Flow\JSONPath\JSONPathLexer('..foo'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_RECURSIVE, $tokens[0]['type']);
        $this->assertEquals('foo', $tokens[0]['value']);
    }


    public function test_Recursive_Wildcard()
    {
        $tokens = (new \Flow\JSONPath\JSONPathLexer('..*'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_RECURSIVE, $tokens[0]['type']);
        $this->assertEquals('*', $tokens[0]['value']);
    }


    /**
     * @expectedException           Flow\JSONPath\JSONPathException
     * @expectedExceptionMessage    Unexpected token ^r at position 4 of expression: ..ba^r
     */
    public function test_Recursive_BadlyFormed()
    {
        $tokens = (new JSONPathLexer('..ba^r'))->parseExpression();
    }


    /**
     */
    public function test_Indexes_Simple()
    {
        $tokens = (new JSONPathLexer('[1,2,3]'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_INDEXES, $tokens[0]['type']);
        $this->assertEquals([1,2,3], $tokens[0]['value']);
    }
    /**
     */
    public function test_Indexes_Whitespace()
    {
        $tokens = (new JSONPathLexer('[ 1,2 , 3]'))->parseExpression();
        $this->assertEquals(JSONPathLexer::T_INDEXES, $tokens[0]['type']);
        $this->assertEquals([1,2,3], $tokens[0]['value']);
    }





}
 