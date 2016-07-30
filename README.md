PSX Json
===

## About

Library which contains classes to handle JSON data. It implements the JSON patch
and pointer specification. The following example shows the basic usage:

## Usage

```php
<?php

$document = Document::fromFile('/test.json');
// or
$document = Document::fromJson('{"author": {"name": "foo"}}');

// get a value through a json pointer
$name = $document->get('/author/name');

// compare whether this document is equal to another document
$document->equals(['foo' => 'bar']);

// apply patch operations on the document
$document->patch([
    (object) ['op' => 'add', 'path' => '/author/uri', 'value' => 'http://google.com'],
]);

// convert the document back to a json string
echo $document->toString();
```

