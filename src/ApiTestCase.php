<?php

namespace Brunty;

use Brunty\ContentTypeNotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ApiTestCase extends TestCase
{

    const XML_CONTENT_TYPE = 'application/xml';
    const JSON_CONTENT_TYPE = 'application/json';

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
        $this->client = new Client(
            [
                'base_uri' => $_ENV['api_base_url']
            ]
        );
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
        self::assertEquals(Response::HTTP_OK, $this->statusCode, sprintf('Status code is not OK, status code is: %s', $this->statusCode));
    }

    public function assertResponseWasSuccess()
    {
        self::assertTrue($this->statusCode >= 200 && $this->statusCode < 300, sprintf('Status code is not a success, status code is: %s', $this->statusCode));
    }

    public function assertResponseWasRedirect()
    {
        self::assertTrue($this->statusCode >= 300 && $this->statusCode < 400, sprintf('Status code is not a redirect, status code is: %s', $this->statusCode));
    }

    public function assertResponseWasClientError()
    {
        self::assertTrue($this->statusCode >= 400 && $this->statusCode < 500, sprintf('Status code is not a client error, status code is: %s', $this->statusCode));
    }

    public function assertResponseWasServerError()
    {
        self::assertTrue($this->statusCode >= 500, sprintf('Status code is not a server error, status code is: %s', $this->statusCode));
    }

    public function assertResponseWasJson()
    {
        self::assertTrue(
            $this->contentTypeIsJson(),
            sprintf('Content type is not "%s", content type is: "%s"', self::JSON_CONTENT_TYPE, $this->getContentType())
        );
    }

    public function assertResponseWasXml()
    {
        self::assertTrue(
            $this->contentTypeIsXml(),
            sprintf('Content type is not "%s", content type is: "%s"', self::XML_CONTENT_TYPE, $this->getContentType())
        );
    }

    public function assertResponseHasKey($key)
    {
        $content = $this->responseBody(true);

        self::assertTrue(isset($content[$key]), sprintf('Response body does not have the key %s', $key));
    }

    /**
     * @return string
     */
    public function rawResponseBody()
    {
        return $this->response->getBody()->getContents();
    }

    /**
     * @param bool $asArray
     *
     * @return array|\stdClass|\SimpleXMLElement
     */
    public function responseBody($asArray = false)
    {
        if ($this->contentTypeIsJson()) {
            return json_decode($this->rawResponseBody(), $asArray);
        }

        if ($this->contentTypeIsXml()) {
            $xml = new \SimpleXMLElement($this->rawResponseBody());
            if ($asArray === true) {
                $xml = json_decode(json_encode($xml), true);
            }

            return $xml;
        }

        throw new ContentTypeNotFoundException(sprintf('Content-Type not recognised: "%s"', $this->getContentType()));
    }

    /**
     * @return mixed
     */
    private function getContentType()
    {
        return $this->response->getHeader('Content-Type')[0];
    }

    /**
     * @return bool
     */
    private function contentTypeIsXml()
    {
        return $this->getContentType() === self::XML_CONTENT_TYPE;
    }

    /**
     * @return bool
     */
    private function contentTypeIsJson()
    {
        return $this->getContentType() === self::JSON_CONTENT_TYPE;
    }
}
