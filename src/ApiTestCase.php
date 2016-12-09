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
     * @return Client
     */
    public function client()
    {
        return $this->client;
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
            $this->response = $e->getResponse();
            $this->statusCode = $e->getResponse()->getStatusCode();
        } catch (ServerException $e) {
            $this->response = $e->getResponse();
            $this->statusCode = $e->getResponse()->getStatusCode();
        }

        return $this->response;
    }

    /**
     * @param $name
     *
     * @return \string[]
     */
    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }

    public function assertResponseOk()
    {
        self::assertEquals(Response::HTTP_OK, $this->statusCode);
    }

    public function assertResponseWasSuccess()
    {
        self::assertTrue($this->statusCode >= 200 && $this->statusCode < 300);
    }

    public function assertResponseWasRedirect()
    {
        self::assertTrue($this->statusCode >= 300 && $this->statusCode < 400);
    }

    public function assertResponseWasClientError()
    {
        self::assertTrue($this->statusCode >= 400 && $this->statusCode < 500);
    }

    public function assertResponseWasServerError()
    {
        self::assertTrue($this->statusCode >= 500);
    }

    public function assertResponseWasJson()
    {
        self::assertTrue($this->getContentType() === 'application/json');
    }

    public function assertResponseWasXml()
    {
        self::assertTrue($this->getContentType() === 'application/xml');
    }

    /**
     * @return mixed
     */
    private function getContentType()
    {
        return $this->response->getHeader('Content-Type')[0];
    }
}
