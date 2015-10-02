<?php
namespace Flow\JSONPath\Test;

require_once __DIR__ . "/../vendor/autoload.php";

use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathLexer;
use \Peekmo\JsonPath\JsonPath as PeekmoJsonPath;

class JSONPathModificationTest extends \PHPUnit_Framework_TestCase
{

    /**
     */
    public function testBasic()
    {
        $data = [
            'foo' => 'bar'
        ];
        (new JSONPath($data))->modify('$.foo', function (& $element) {
            $element = 'asdf';
        });
        $this->assertEquals('asdf', $data['foo']);
    }

    /**
     */
    public function testUnintentionalReference()
    {
        $data = [
            'foo' => 'bar'
        ];
        $arr = (new JSONPath($data))->find('$.foo');
        $arr[0] = 'asdf';
        $this->assertEquals('asdf', $arr[0]);
        $this->assertEquals('bar', $data['foo']);
    }

    public function testBasic2()
    {
        $data = [
            'foo' => 'bar',
            'foo2' => 'baz',
            'foo3' => 'fiz'
        ];

        (new JSONPath($data))->modify('$.*', function (& $element, $key) {
            $element = 'asdf';
        });

        $this->assertEquals(['asdf','asdf','asdf'], [$data['foo'],$data['foo2'],$data['foo3']]);
    }


    public function testBasic3()
    {
        $data2 = [
            'foo' => [
                'bar' => 1,
                'baz' => 1
            ]
        ];

        (new JSONPath($data2))->modify('$.foo.*', function (& $element, $i) {
            $element = $i * 2;
        });

        $this->assertEquals(0, $data2['foo']['bar']);
        $this->assertEquals(2, $data2['foo']['baz']);
    }

    public function testQueryMatch()
    {
        $data2 = [
            ['name' => 'bob', 'age' => 30],
            ['name' => 'jane', 'age' => 31],
        ];

        (new JSONPath($data2))->modify('$[?(@.name="bob")]', function (& $element) {
            $element['age'] = 50;
        });

        $this->assertEquals(50, $data2[0]['age']);
    }

    public function testRecursiveFilter()
    {
        $data2 = [
            [
                ['name' => 'bob', 'age' => 30],
                ['name' => 'jane', 'age' => 31],
            ]
        ];

        (new JSONPath($data2))->modify('$..[?(@.name="bob")]', function (& $element) {
            $element['age'] = 50;
        });

        $this->assertEquals(50, $data2[0][0]['age']);
    }

    public function testIndexesFilter()
    {
        $data = [
            'bar', 'baz', 'fiz'
        ];

        (new JSONPath($data))->modify('$[0,2]', function (& $element) {
            $element = 'asdf';
        });

        $this->assertEquals(['asdf','baz','asdf'], $data);

    }

    public function testSliceFilter()
    {
        $data = [
            'bar', 'baz', 'fiz'
        ];

        (new JSONPath($data))->modify('$[1:2]', function (& $element) {
            $element = 'asdf';
        });

        $this->assertEquals(['bar','asdf','asdf'], $data);
    }

    public function testQueryResultFilter()
    {
        $data = [
            'bar', 'baz', 'fiz'
        ];

        (new JSONPath($data))->modify('$[1:2]', function (& $element) {
            $element = 'asdf';
        });

        $this->assertEquals(['bar','asdf','asdf'], $data);
    }

    public function testChainedModification()
    {
        $data2 = [
            'foo' => [
                'bar' => 1,
                'baz' => 1
            ]
        ];

        $result1 = (new JSONPath($data2))->modify('$.foo');
        $result2 = $result1->modify('$.[0].bar', function (& $element) {
            $element = 5;
        });

        $this->assertEquals(5, $data2['foo']['bar']);
    }

}