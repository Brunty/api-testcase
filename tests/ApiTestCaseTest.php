<?php

namespace Brunty\Tests;

use Brunty\ApiTestCase;

class ApiTestCaseTest extends ApiTestCase
{

    public function setUp()
    {
        parent::setUp();
    }
    /**
     * @test
     */
    public function it_gets_an_endpoint()
    {
        $this->get('/get');
        $this->assertResponseOk();
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
    public function it_asserts_the_response_is_json()
    {
        $this->get('/get');
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
}
