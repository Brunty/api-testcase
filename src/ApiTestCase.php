<?php

namespace Brunty;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ApiTestCase extends TestCase
{

    /**
     * @var Client;
     */
    private $client;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var int
     */
    private $statusCode;

    public function setUp()
    {
        $this->client = new Client([
            'base_uri' => $_ENV['api_base_url']
        ]);
    }

    /**
     * @param       $path
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function get($path, array $options = [])
    {
        try {
            $this->response = $this->client->get($path, $options);
            $this->statusCode = $this->response->getStatusCode();
        } catch (ClientException $e) {
            $this->statusCode = $e->getCode();
        } catch (ServerException $e) {
            $this->statusCode = $e->getCode();
        }

        return $this->response;
    }

    public function assertRequestOk()
    {
        self::assertEquals(Response::HTTP_OK, $this->statusCode);
    }

    public function assertRequestWasSuccess()
    {
        self::assertTrue($this->statusCode >= 200 && $this->statusCode < 300);
    }

    public function assertRequestWasRedirect()
    {
        self::assertTrue($this->statusCode >= 300 && $this->statusCode < 400);
    }

    public function assertRequestWasClientError()
    {
        self::assertTrue($this->statusCode >= 400 && $this->statusCode < 500);
    }


    public function assertRequestWasServerError()
    {
        self::assertTrue($this->statusCode >= 500);
    }
}
