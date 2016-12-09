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
        $this->assertRequestOk();
    }

    /**
     * @test
     * @dataProvider provider_for_it_asserts_that_a_successful_request_was_made
     *
     * @param int $statusCode
     */
    public function it_asserts_that_a_successful_request_was_made($statusCode)
    {
        $this->get(sprintf('/status/%d', $statusCode));
        $this->assertRequestWasSuccess();
    }

    /**
     * @return array
     */
    public function provider_for_it_asserts_that_a_successful_request_was_made()
    {
        return [
            [200],
            [201],
            [299]
        ];
    }

    /**
     * @test
     * @dataProvider provider_for_it_asserts_that_a_redirect_request_was_made
     *
     * @param int $statusCode
     */
    public function it_asserts_that_a_redirect_request_was_made($statusCode)
    {
        $this->get(sprintf('/status/%d', $statusCode), ['allow_redirects' => false]);
        $this->assertRequestWasRedirect();
    }

    /**
     * @return array
     */
    public function provider_for_it_asserts_that_a_redirect_request_was_made()
    {
        return [
            [300],
            [301],
            [399]
        ];
    }

    /**
     * @test
     * @dataProvider provider_for_it_asserts_that_a_client_error_request_was_made
     *
     * @param int $statusCode
     */
    public function it_asserts_that_a_client_error_request_was_made($statusCode)
    {
        $this->get(sprintf('/status/%d', $statusCode));
        $this->assertRequestWasClientError();
    }

    /**
     * @return array
     */
    public function provider_for_it_asserts_that_a_client_error_request_was_made()
    {
        return [
            [400],
            [401],
            [451]
        ];
    }
    
    /**
     * @test
     * @dataProvider provider_for_it_asserts_that_a_server_error_request_was_made
     *
     * @param int $statusCode
     */
    public function it_asserts_that_a_server_error_request_was_made($statusCode)
    {
        $this->get(sprintf('/status/%d', $statusCode));
        $this->assertRequestWasServerError();
    }

    /**
     * @return array
     */
    public function provider_for_it_asserts_that_a_server_error_request_was_made()
    {
        return [
            [500],
            [501],
            [599]
        ];
    }
}
