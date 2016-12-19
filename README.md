# Brunty\ApiTestCase

[![Build Status](https://travis-ci.org/Brunty/api-testcase.svg?branch=master)](https://travis-ci.org/Brunty/api-testcase) [![Coverage Status](https://coveralls.io/repos/github/Brunty/api-testcase/badge.svg?branch=master)](https://coveralls.io/github/Brunty/api-testcase?branch=master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/53748ffe-c2de-48f6-b0be-fffa9af7c39e/mini.png)](https://insight.sensiolabs.com/projects/53748ffe-c2de-48f6-b0be-fffa9af7c39e)

Just some basic helper stuff to help test API endpoints.

## Compatibility

* PHP 5.6 and above
* PHPUnit 5.7 and above
* Guzzlehttp 6.2 and above

## Installation

`composer require brunty/api-testcase --dev`

## Usage

Add an environment variable to your PHPUnit Configuration that's your API's base URL:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <php>
        <env name="api_base_url" value="http://httpbin.org"/>
    </php>
</phpunit>
```

Extend the `\Brunty\ApiTestCase` class. If you need to configure the client, call `$this->configureClientOptions($options);` before calling `parent::setUp()`:

```php
<?php

use Brunty\ApiTestCase;

class BooksApiTest extends ApiTestCase
{
    public function setUp()
    {
        $options = [
            // ...
        ];
        
        // use this if you want to add additional options to the client when it's constructed
        $this->configureClientOptions($options);
        parent::setUp();
    }
}
```

## Methods and requests available

The test case uses [Guzzle](http://docs.guzzlephp.org/en/latest/index.html) (`\GuzzleHttp\Client`) under the surface, so requests are effectively just made through that. If you need to access the client, you can do so with `$this->client();` within your test class.

### GET

`get(string $path [, array $options])`

```php
<?php

use Brunty\ApiTestCase;

class BooksApiTest extends ApiTestCase
{
    /**
     * @test
     */
    public function the_api_retrieves_all_books()
    {
        $this->get('/books');
        $this->assertResponseOk();
    }
}
```

### POST

`post(string $path [, array $options])`

```php
<?php

use Brunty\ApiTestCase;

class BooksApiTest extends ApiTestCase
{
    /**
     * @test
     */
    public function the_api_creates_a_book()
    {
        $this->post('/books', ['title' => 'My Book']);
        $this->assertResponseOk();
    }
}
```

### PATCH

`patch(string $path [, array $options])`

```php
<?php

use Brunty\ApiTestCase;

class BooksApiTest extends ApiTestCase
{
    /**
     * @test
     */
    public function the_api_updates_a_book()
    {
        $this->patch('/books/1', ['title' => 'My Updated Book']);
        $this->assertResponseOk();
    }
}
```

### PUT

`put(string $path [, array $options])`

```php
<?php

use Brunty\ApiTestCase;

class BooksApiTest extends ApiTestCase
{
    /**
     * @test
     */
    public function the_api_creates_or_updates_a_book()
    {
        $this->put('/books', ['title' => 'My Updated Book']);
        $this->assertResponseOk();
    }
}
```

### DELETE

`delete(string $path [, array $options])`

```php
<?php

use Brunty\ApiTestCase;

class BooksApiTest extends ApiTestCase
{
    /**
     * @test
     */
    public function the_api_deletes_a_book()
    {
        $this->delete('/books/1');
        $this->assertResponseOk();
    }
}
```

### Headers & Responses

`getHeader(string $name)`

Returns a response header matching the name.

`response()`

Returns the response object.

`statusCode()`

Returns the status code from the response.

`rawResponseBody()`

Returns the contents of the body of the response.

`responseBody($asArray)`

Returns the response body, parsed into either an array (if `$asArray` is true) or: `\stdClass` if the response was JSON, `\SimpleXmlElement` if the response was XML.

If the content type of the response cannot be determined to be either XML or JSON, a `\Brunty\ContentTypeNotFound` exception will be thrown.

`getContentType()`

Returns the value of the first `Content-Type` header element.

`contentTypeIsXml()`

Returns `true` if the content type is XML, `false` otherwise.

`contentTypeIsJson()`

Returns `true` if the content type is JSON, `false` otherwise.

The `\Brunty\Response` class contains a list of constants for all HTTP status codes - these can help make status code assertions more readable - for example:

`$this->assertResponseStatus(\Brunty\Response::HTTP_NO_CONTENT);` as opposed to `$this->assertResponseStatus(204);`

### Assertions

| Assertion        | Notes|
|:------------- |:-------------|
| `assertResponseStatus($status)` | |
| `assertResponseOk()` | (Response code 200) |
| `assertResponseWasSuccess()` | (200 <= Response Code < 300) |
| `assertResponseWasRedirect()` | (300 <= Response Code < 400) _Note that you may need to set the `allow_redirects` option to `false` otherwise status codes of the page after the redirect can be used._ | 
|  `assertResponseWasClientError()` | (400 <= Response Code < 500) |
|  `assertResponseWasServerError()` | (500 <= Response Code) |
|  `assertResponseWasJson()` | |
|  `assertResponseWasXml()` | |
|  `assertResponseHasKey($key)` | |
|  `assertNodeIsValue($xPathQuery, $value)` | Runs the xpath query against the result (yes, even for JSON - though | that's a bit experimental) and asserts that the value is correct - currently only works with strings.
|  `assertRedirectedTo($path)` | Path can be absolute, or relative to the root `api_base_url` |

## Contributing

This started as a project of boredom one Friday evening, if you find yourself using this, and want more features, please feel free to suggest them, or submit a PR!

Although this project is small, openness and inclusivity are taken seriously. To that end the following code of conduct has been adopted.

[Contributor Code of Conduct](CONTRIBUTING.md)
