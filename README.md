# Api TestCase

[![Build Status](https://travis-ci.org/Brunty/api-testcase.svg?branch=master)](https://travis-ci.org/Brunty/api-testcase) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/53748ffe-c2de-48f6-b0be-fffa9af7c39e/mini.png)](https://insight.sensiolabs.com/projects/53748ffe-c2de-48f6-b0be-fffa9af7c39e)

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

Extend the `\Brunty\ApiTestCase` class:

```php
<?php

use Brunty\ApiTestCase;

class BooksApiTest extends ApiTestCase
{

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

### Headers

`getHeader(string $name)`

Returns a response header matching the name.

### Assertions

* `assertResponseOk` (Response code 200)
* `assertResponseWasSuccess` (200 <= Response Code < 300)
  * Note that you may need to set the `allow_redirects` option to `false` otherwise status codes of the page after the redirect can be used. 
* `assertResponseWasRedirect` (300 <= Response Code < 400)
* `assertResponseWasClientError` (400 <= Response Code < 500)
* `assertResponseWasServerError` (500 <= Response Code)
* `assertResponseWasJson`
* `assertResponseWasXml`




## Contributing

Although this project is small, openness and inclusivity are taken seriously. To that end the following code of conduct has been adopted.

[Contributor Code of Conduct](CONTRIBUTING.md)
