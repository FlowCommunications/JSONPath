<?php
namespace Flow\JSONPath;

require_once __DIR__ . "/../vendor/autoload.php";

use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathLexer;
use \Peekmo\JsonPath\JsonPath as PeekmoJsonPath;

class JSONPathTest extends \PHPUnit_Framework_TestCase
{

    /**
     * $.store.books[0].title
     */
    public function testChildOperators()
    {
        $result = (new JSONPath($this->exampleData()))->find('$.store.books[0].title');
        $this->assertEquals('Sayings of the Century', $result[0]);
    }

    /**
     * $['store']['books'][0]['title']
     */
    public function testChildOperatorsAlt()
    {
        $result = (new JSONPath($this->exampleData()))->find("$['store']['books'][0]['title']");
        $this->assertEquals('Sayings of the Century', $result[0]);
    }

    /**
     * $.array[start:end:step]
     */
    public function testFilterSliceA()
    {
        // Copy all items... similar to a wildcard
        $result = (new JSONPath($this->exampleData()))->find("$['store']['books'][:].title");
        $this->assertEquals(['Sayings of the Century', 'Sword of Honour', 'Moby Dick', 'The Lord of the Rings'], $result);
    }

    public function testFilterSliceB()
    {
        // Fetch every second item starting with the first index (odd items)
        $result = (new JSONPath($this->exampleData()))->find("$['store']['books'][1::2].title");
        $this->assertEquals(['Sword of Honour', 'The Lord of the Rings'], $result);
    }

    public function testFilterSliceC()
    {
        // Fetch up to the second index
        $result = (new JSONPath($this->exampleData()))->find("$['store']['books'][0:2:1].title");
        $this->assertEquals(['Sayings of the Century', 'Sword of Honour', 'Moby Dick'], $result);
    }

    public function testFilterSliceD()
    {
        // Fetch up to the second index
        $result = (new JSONPath($this->exampleData()))->find("$['store']['books'][-1:].title");
        $this->assertEquals(['The Lord of the Rings'], $result);
    }

    /**
     * Everything except the last 2 items
     */
    public function testFilterSliceE()
    {
        // Fetch up to the second index
        $result = (new JSONPath($this->exampleData()))->find("$['store']['books'][:-2].title");
        $this->assertEquals(['Sayings of the Century', 'Sword of Honour'], $result);
    }

    /**
     * The Last item
     */
    public function testFilterSliceF()
    {
        // Fetch up to the second index
        $result = (new JSONPath($this->exampleData()))->find("$['store']['books'][-1].title");
        $this->assertEquals(['The Lord of the Rings'], $result);
    }

    /**
     * $.store.books[(@.length-1)].title
     *
     * This notation is only partially implemented eg. hacked in
     */
    public function testChildQuery()
    {
        $result = (new JSONPath($this->exampleData()))->find("$.store.books[(@.length-1)].title");
        $this->assertEquals(['The Lord of the Rings'], $result);
    }

    /**
     * $.store.books[?(@.price < 10)].title
     * Filter books that have a price less than 10
     */
    public function testQueryMatchLessThan()
    {
        $result = (new JSONPath($this->exampleData()))->find("$.store.books[?(@.price < 10)].title");
        $this->assertEquals(['Sayings of the Century', 'Moby Dick'], $result);
    }

    /**
     * $..books[?(@.author == "J. R. R. Tolkien")]
     * Filter books that have a title equal to "..."
     */
    public function testQueryMatchEquals()
    {
        $results = (new JSONPath($this->exampleData()))->find('$..books[?(@.author == "J. R. R. Tolkien")].title');
        $this->assertEquals($results[0], 'The Lord of the Rings');
    }

    /**
     * $.store.books[*].author
     */
    public function testWildcardAltNotation()
    {
        $result = (new JSONPath($this->exampleData()))->find("$.store.books[*].author");
        $this->assertEquals(['Nigel Rees', 'Evelyn Waugh', 'Herman Melville', 'J. R. R. Tolkien'], $result);
    }

    /**
     * $..author
     */
    public function testRecursiveChildSearch()
    {
        $result = (new JSONPath($this->exampleData()))->find("$..author");
        $this->assertEquals(['Nigel Rees', 'Evelyn Waugh', 'Herman Melville', 'J. R. R. Tolkien'], $result);
    }

    /**
     * $.store.*
     * all things in store
     * the structure of the example data makes this test look weird
     */
    public function testWildCard()
    {
        $result = (new JSONPath($this->exampleData()))->find("$.store.*");
        if (is_object($result[0][0])) {
            $this->assertEquals('Sayings of the Century', $result[0][0]->title);
        } else {
            $this->assertEquals('Sayings of the Century', $result[0][0]['title']);
        }

        if (is_object($result[1])) {
            $this->assertEquals('red', $result[1]->color);
        } else {
            $this->assertEquals('red', $result[1]['color']);
        }
    }

    /**
     * $.store..price
     * the price of everything in the store.
     */
    public function testRecursiveChildSearchAlt()
    {
        $result = (new JSONPath($this->exampleData()))->find("$.store..price");
        $this->assertEquals([8.95, 12.99, 8.99, 22.99, 19.95], $result);
    }

    /**
     * $..books[2]
     * the third book
     */
    public function testRecursiveChildSearchWithChildIndex()
    {
        $result = (new JSONPath($this->exampleData()))->find("$..books[2].title");
        $this->assertEquals(["Moby Dick"], $result);
    }

    /**
     * $..books[(@.length-1)]
     */
    public function testRecursiveChildSearchWithChildQuery()
    {
        $result = (new JSONPath($this->exampleData()))->find("$..books[(@.length-1)].title");
        $this->assertEquals(["The Lord of the Rings"], $result);
    }

    /**
     * $..books[-1:]
     * Resturn the last results
     */
    public function testRecursiveChildSearchWithSliceFilter()
    {
        $result = (new JSONPath($this->exampleData()))->find("$..books[-1:].title");
        $this->assertEquals(["The Lord of the Rings"], $result);
    }

    /**
     * $..books[?(@.isbn)]
     * filter all books with isbn number
     */
    public function testRecursiveWithQueryMatch()
    {
        $result = (new JSONPath($this->exampleData()))->find("$..books[?(@.isbn)].isbn");

        $this->assertEquals(['0-553-21311-3', '0-395-19395-8'], $result);
    }

    /**
     * $..*
     * All members of JSON structure
     */
    public function testRecursiveWithWildcard()
    {
        $result = (new JSONPath($this->exampleData()))->find("$..*");
        $result = json_decode(json_encode($result), true);

        $this->assertEquals('Sayings of the Century', $result[0]['books'][0]['title']);
        $this->assertEquals(19.95, $result[26]);
    }

    public function testFilteringOnNoneArrays()
    {
        $data = ['foo' => 'asdf'];

        $result = (new JSONPath($data))->find("$.foo.bar");
        $this->assertEquals([], $result);
    }

    public function testBenchmark()
    {
        $goessnerJsonPath = new PeekmoJsonPath;
        $exampleData = $this->exampleData();

        $start1 = microtime(true);
        for ($i = 0; $i < 1; $i += 1) {
            $results1 = $goessnerJsonPath->jsonPath($exampleData, '$.store.books[?(@.price < 10)]');
        }
        $end1 = microtime(true);

        $start2 = microtime(true);
        for ($i = 0; $i < 1; $i += 1) {
            $results2 = (new JSONPath($exampleData))->find('$.store.books[?(@.price < 10)]');
        }
        $end2 = microtime(true);

        $this->assertEquals($results1, $results2);

        echo "Old JsonPath: " . ($end1 - $start1) . PHP_EOL;
        echo "JSONPath: " . ($end2 - $start2) . PHP_EOL;
    }

    public function testBenchmark2()
    {
        $goessnerJsonPath = new PeekmoJsonPath;
        $exampleData = $this->exampleData();

        $start1 = microtime(true);
        for ($i = 0; $i < 1; $i += 1) {
            $results1 = $goessnerJsonPath->jsonPath($exampleData, '$.store.*');
        }
        $end1 = microtime(true);

        $start2 = microtime(true);
        for ($i = 0; $i < 1; $i += 1) {
            $results2 = (new JSONPath($exampleData))->find('$.store.*');
        }
        $end2 = microtime(true);

        $this->assertEquals($results1, $results2);

        echo "Old JsonPath: " . ($end1 - $start1) . PHP_EOL;
        echo "JSONPath: " . ($end2 - $start2) . PHP_EOL;
    }

    public function testBenchmark3()
    {
        $goessnerJsonPath = new PeekmoJsonPath;
        $exampleData = $this->exampleData();

        $start1 = microtime(true);
        for ($i = 0; $i < 1; $i += 1) {
            $results1 = $goessnerJsonPath->jsonPath($exampleData, '$..*');
        }
        $end1 = microtime(true);

        $start2 = microtime(true);
        for ($i = 0; $i < 1; $i += 1) {
            $results2 = (new JSONPath($exampleData))->find('$..*');
        }
        $end2 = microtime(true);

        $this->assertEquals($results1, $results2);

        echo "Old JsonPath: " . ($end1 - $start1) . PHP_EOL;
        echo "JSONPath: " . ($end2 - $start2) . PHP_EOL;
    }

    public function testBenchmark4()
    {
        $goessnerJsonPath = new PeekmoJsonPath;
        $exampleData = $this->exampleData();

        $start1 = microtime(true);
        for ($i = 0; $i < 100; $i += 1) {
            $results1 = $goessnerJsonPath->jsonPath($exampleData, '$..price');
        }
        $end1 = microtime(true);

        $exampleData = $this->exampleData(true);

        $start2 = microtime(true);
        for ($i = 0; $i < 100; $i += 1) {
            $results2 = (new JSONPath($exampleData))->find('$..price');
        }
        $end2 = microtime(true);

        $this->assertEquals($results1, $results2);

        echo "Old JsonPath: " . ($end1 - $start1) . PHP_EOL;
        echo "JSONPath: " . ($end2 - $start2) . PHP_EOL;
    }

    public function testMagicMethods()
    {
        $fooClass = new JSONPathTestClass();

        $results = (new JSONPath($fooClass, JSONPath::ALLOW_MAGIC))->find('$.foo');

        $this->assertEquals(['bar'], $results);
    }

    public function testRecursiveQueryMatchWithSquareBrackets()
    {
        $result = (new JSONPath($this->exampleDataExtra()))->find("$['http://www.w3.org/2000/01/rdf-schema#label'][?(@['@language']='en')]['@language']");
        $this->assertEquals(["en"], $result);
    }


    public function exampleData($asArray = true)
    {
        $json = <<<JSON
        {
          "store":{
            "books":[
              {
                "category":"reference",
                "author":"Nigel Rees",
                "title":"Sayings of the Century",
                "price":8.95
              },
              {
                "category":"fiction",
                "author":"Evelyn Waugh",
                "title":"Sword of Honour",
                "price":12.99
              },
              {
                "category":"fiction",
                "author":"Herman Melville",
                "title":"Moby Dick",
                "isbn":"0-553-21311-3",
                "price":8.99
              },
              {
                "category":"fiction",
                "author":"J. R. R. Tolkien",
                "title":"The Lord of the Rings",
                "isbn":"0-395-19395-8",
                "price":22.99
              }
            ],
            "bicycle":{
              "color":"red",
              "price":19.95
            }
          }
        }
JSON;
        return json_decode($json, $asArray);
    }

    public function exampleDataExtra($asArray = true)
    {
        $json = <<<JSON
{
   "http://www.w3.org/2000/01/rdf-schema#label":[
      {
         "@language":"en"
      },
      {
         "@language":"de"
      }
   ]
}
JSON;
        return json_decode($json, $asArray);

    }
}

class JSONPathTestClass
{
    protected $attributes = [
        'foo' => 'bar'
    ];

    public function __get($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }
}