PSX Json
===

## About

Library which contains classes to handle JSON data. It implements the JSON patch
and pointer specification and provides a simple JSON RPC server.

## Usage

### Patch/Pointer

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

### RPC Server

The following example shows a super simple JSON RPC server using plain PHP.
It is also easy possible to integrate the server into any existing framework.

```php
<?php

use PSX\Json\RPC\Server;
use PSX\Json\RPC\Exception\MethodNotFoundException;

$server = new Server(function($method, $arguments){
    if ($method === 'sum') {
        return array_sum($arguments);
    } else {
        throw new MethodNotFoundException('Method not found');
    }
});

$return = $server->invoke(\json_decode(file_get_contents('php://input')));

header('Content-Type: application/json');
echo \json_encode($return, JSON_PRETTY_PRINT);

```
