<?php

namespace Brunty\Tests;

use Brunty\ApiTestCase;
use Brunty\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class ApiTestCaseTest extends ApiTestCase
{

    /**
     * @test
     * @dataProvider provider_for_it_makes_http_requests
     *
     * @param $method
     * @param $path
     */
    public function it_makes_http_requests($method, $path)
    {
        $this->$method($path);
        $this->assertResponseOk();
    }

    /**
     * @return array
     */
    public function provider_for_it_makes_http_requests()
    {
        return [
            ['get', '/get'],
            ['put', '/put'],
            ['post', '/post'],
            ['patch', '/patch'],
            ['delete', '/delete'],
        ];
    }

    /**
     * @test
     */
    public function it_asserts_that_a_response_matches()
    {
        $this->get('/status/201');
        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    /**
     * @test
     * @dataProvider provider_for_it_asserts_that_a_successful_response_was_returned
     *
     * @param int $statusCode
     */
    public function it_asserts_that_a_successful_response_was_returned($statusCode)
    {
        $this->get(sprintf('/status/%d', $statusCode));
        $this->assertResponseWasSuccess();
    }

    /**
     * @return array
     */
    public function provider_for_it_asserts_that_a_successful_response_was_returned()
    {
        return [
            [200],
            [201],
            [299]
        ];
    }

    /**
     * @test
     * @dataProvider provider_for_it_asserts_that_a_redirect_response_was_returned
     *
     * @param int $statusCode
     */
    public function it_asserts_that_a_redirect_response_was_returned($statusCode)
    {
        $this->get(sprintf('/status/%d', $statusCode), ['allow_redirects' => false]);
        $this->assertResponseWasRedirect();
    }

    /**
     * @return array
     */
    public function provider_for_it_asserts_that_a_redirect_response_was_returned()
    {
        return [
            [300],
            [301],
            [399]
        ];
    }

    /**
     * @test
     * @dataProvider provider_for_it_asserts_that_a_client_error_response_was_returned
     *
     * @param int $statusCode
     */
    public function it_asserts_that_a_client_error_response_was_returned($statusCode)
    {
        $this->get(sprintf('/status/%d', $statusCode));
        $this->assertResponseWasClientError();
    }

    /**
     * @return array
     */
    public function provider_for_it_asserts_that_a_client_error_response_was_returned()
    {
        return [
            [400],
            [401],
            [451]
        ];
    }

    /**
     * @test
     * @dataProvider provider_for_it_asserts_that_a_server_error_response_was_returned
     *
     * @param int $statusCode
     */
    public function it_asserts_that_a_server_error_response_was_returned($statusCode)
    {
        $this->get(sprintf('/status/%d', $statusCode));
        $this->assertResponseWasServerError();
    }

    /**
     * @return array
     */
    public function provider_for_it_asserts_that_a_server_error_response_was_returned()
    {
        return [
            [500],
            [501],
            [599]
        ];
    }

    /**
     * @test
     */
    public function it_gets_the_headers_from_a_response()
    {
        $this->get('/response-headers?X-Test-Header=test header 1&X-Test-Header=another header here');
        $headers = [
            'test header 1',
            'another header here'
        ];
        self::assertEquals($headers, $this->getHeader('X-Test-Header'));
    }

    /**
     * @test
     */
    public function it_asserts_the_value_of_a_node_in_a_json_response()
    {
        $this->get('/response-headers?X-Foo=Bar');
        self::assertNodeIsValue('//X-Foo[1]', 'Bar');
    }

    /**
     * @test
     */
    public function it_asserts_the_value_of_a_node_in_an_xml_response()
    {
        $this->get('/xml');
        self::assertNodeIsValue('//slide[1]/title', 'Wake up to WonderWidgets!');
    }

    /**
     * @test
     */
    public function it_asserts_the_value_of_a_node_in_an_xml_response_is_null_if_it_does_not_exist()
    {
        $this->get('/xml');
        self::assertNodeIsValue('//slide[43]//foo/bar/title', null);
    }

    /**
     * @test
     */
    public function it_asserts_that_you_were_redirected_correctly_with_absolute_url()
    {
        $this->get('/status/301');
        $this->assertRedirectedTo('http://httpbin.org/get');
    }

    /**
     * @test
     */
    public function it_asserts_that_you_were_redirected_correctly_with_relative_url()
    {
        $this->get('/status/301');
        $this->assertRedirectedTo('/get');
    }

    /**
     * @test
     */
    public function it_asserts_the_response_is_json()
    {
        $this->get('/get');
        $this->assertResponseWasJson();

        $this->get('/response-headers?Content-Type=application/json; charset=UTF-8');
        $this->assertResponseWasJson();
    }

    /**
     * @test
     */
    public function it_asserts_the_response_is_xml()
    {
        $this->get('/xml');
        $this->assertResponseWasXml();
    }

    /**
     * @test
     * @dataProvider provider_for_it_gets_the_response_body_as_an_array
     *
     * @param $endPoint
     */
    public function it_gets_the_response_body_as_an_array($endPoint)
    {
        $this->get($endPoint);
        self::assertTrue(is_array($this->responseBody(true)));
    }

    /**
     * @return array
     */
    public function provider_for_it_gets_the_response_body_as_an_array()
    {
        return [
            ['/get'],
            ['/xml']
        ];
    }

    /**
     * @test
     * @dataProvider provider_for_it_gets_the_response_body_as_an_object
     *
     * @param $endPoint
     * @param $class
     */
    public function it_gets_the_response_body_as_an_object($endPoint, $class)
    {
        $this->get($endPoint);
        self::assertInstanceOf($class, $this->responseBody());
    }

    /**
     * @return array
     */
    public function provider_for_it_gets_the_response_body_as_an_object()
    {
        return [
            ['/get', \stdClass::class],
            ['/xml', \SimpleXMLElement::class]
        ];
    }

    /**
     * @test
     * @expectedException \Brunty\ContentTypeNotFoundException
     */
    public function it_throws_an_exception_if_the_content_type_is_not_known()
    {
        $this->get('/html');
        $this->responseBody();
    }

    /**
     * @test
     */
    public function it_asserts_the_response_has_a_key()
    {
        $this->get('/response-headers?X-Test-Header=testHeader1');
        $this->assertResponseHasKey('X-Test-Header');
    }

    /**
     * @test
     */
    public function it_returns_the_client()
    {
        self::assertInstanceOf(Client::class, $this->client());
    }

    /**
     * @test
     */
    public function it_returns_the_response_and_status_code()
    {
        $this->get('/get');
        self::assertInstanceOf(GuzzleResponse::class, $this->response());
        self::assertSame(Response::HTTP_OK, $this->statusCode());
    }
}
