JSONPath
=============

This is a [JSONPath](http://goessner.net/articles/JsonPath/) implementation for PHP based on Stefan Goessner's JSONPath script.

JSONPath is an XPath-like expression language for filtering, flattening and extracting data.

I believe that is improves on the original script (which was last updated in 2007) by parsing a set of tokens using
some fancy RegEx techniques appropriated from the Doctrine Lexer library. These tokens are cached in a static variable and performance is
generally about 2&ndash;5 &times; faster (I haven't gone to explore why it is quicker but that's just how it turned out). Lastly, there is no `eval()` anywhere in sight (hoorah).

JSONPath Examples
---

JSONPath                | Result
------------------------|-------------------------------------
$.store.books[\*].author | the authors of all books in the store
$..author               | all authors
$.store..price          | the price of everything in the store.
$..books[2]             | the third book
$..books[(@.length-1)]  | the last book in order.
$..books[0,1]           | the first two books
$..books[:2]            | the first two books
$..books[?(@.isbn)]     | filter all books with isbn number
$..books[?(@.price<10)] | filter all books cheapier than 10
$..*                    | all elements in the data (recursively extracted)


Expression syntax
---

Symbol              | Description
--------------------|-------------------------
$                   | The root object/element (not strictly necessary)
@                   | The current object/element
. or []             | Child operator
..                  | Recursive descent
*                   | Wildcard. All child elements regardless their index.
[,]                 | Array indices as a set
[start:end:step]    | Array slice operator borrowed from ES4/Python.
?()                 | Filters a result set by a script expression
()                  | Uses the result of a script expression as the index

PHP Usage
---

```php
$data = ['people' => [['name' => 'Joe'], ['name' => 'Jane'], ['name' => 'John']]];
$result = (new JSONPath($data))->find('$.people.*.name');
// $result === ['Joe', 'Jane', 'John']
```

For more examples, check the JSONPathTest.php tests file.

Caveats
-------
-   Only arrays are supported at this point which means you will need to stick to `json_decode($json, true)`. I intend to add support for objects at this point but this is just my first pass at this library.
-   "script expressions" are not *exactly* supported but there is some functionality that mimicks scripting (read below)

The one thing that this implementation does not fully support is the "script expression using the underlying script engine". You could call me overly-cautious but I don't want to eval anything in PHP. So here are the types of query expressions that are supported:

	[?(@._KEY_ _OPERATOR_ _VALUE_)] // <, >, !=, and ==
	Eg.
	[?(@.title == "A string")] //
	[?(@.title = "A string")]
	// A single equals is not an assignment but the SQL-style of '=='

Similar projects
----------------

The [Hash](http://book.cakephp.org/2.0/en/core-utility-libraries/hash.html) utility from CakePHP does some similar things 

The original JsonPath implementations is available at [http://code.google.com/p/jsonpath]() and re-hosted for composer here [Peekmo/JsonPath](https://github.com/Peekmo/JsonPath).
