<?php

namespace Brunty;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;

class ApiTestCase extends TestCase
{

    const XML_CONTENT_TYPE = 'application/xml';
    const JSON_CONTENT_TYPE = 'application/json';

    /**
     * @var Client;
     */
    private $client;

    /**
     * @var GuzzleResponse
     */
    private $response;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var array
     */
    private $clientOptions = [];

    public function configureClientOptions(array $options = [])
    {
        $this->clientOptions = $options;
    }

    public function setUp()
    {
        $this->client = new Client(
            $this->clientOptions + [
                'base_uri' => $this->baseUrl()
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
     * @return Response
     */
    public function response()
    {
        return $this->response();
    }

    /**
     * @return mixed
     */
    public function statusCode()
    {
        return $this->statusCode();
    }

    /**
     * @param string $path
     * @param array  $options
     */
    public function get($path, array $options = [])
    {
        $this->request('get', $path, $options);
    }

    /**
     * @param string $path
     * @param array  $options
     */
    public function post($path, array $options = [])
    {
        $this->request('post', $path, $options);
    }

    /**
     * @param string $path
     * @param array  $options
     */
    public function patch($path, array $options = [])
    {
        $this->request('patch', $path, $options);
    }

    /**
     * @param string $path
     * @param array  $options
     */
    public function put($path, array $options = [])
    {
        $this->request('put', $path, $options);
    }

    /**
     * @param string $path
     * @param array  $options
     */
    public function delete($path, array $options = [])
    {
        $this->request('delete', $path, $options);
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

    /**
     * @param int $status
     */
    public function assertResponseStatus($status)
    {
        self::assertEquals(
            $status,
            $this->statusCode,
            sprintf('Expected response status %s not found. Response status is: %s', $status, $this->statusCode)
        );
    }

    public function assertResponseOk()
    {
        self::assertEquals(
            Response::HTTP_OK,
            $this->statusCode,
            sprintf('Status code is not OK, status code is: %s', $this->statusCode)
        );
    }

    public function assertResponseWasSuccess()
    {
        self::assertTrue(
            $this->statusCode >= 200 && $this->statusCode < 300,
            sprintf('Status code is not a success, status code is: %s', $this->statusCode)
        );
    }

    public function assertResponseWasRedirect()
    {
        self::assertTrue(
            $this->statusCode >= 300 && $this->statusCode < 400,
            sprintf('Status code is not a redirect, status code is: %s', $this->statusCode)
        );
    }

    public function assertResponseWasClientError()
    {
        self::assertTrue(
            $this->statusCode >= 400 && $this->statusCode < 500,
            sprintf('Status code is not a client error, status code is: %s', $this->statusCode)
        );
    }

    public function assertResponseWasServerError()
    {
        self::assertTrue(
            $this->statusCode >= 500,
            sprintf('Status code is not a server error, status code is: %s', $this->statusCode)
        );
    }

    /**
     * @param $path
     */
    public function assertRedirectedTo($path)
    {
        $headers = $this->response->getHeader('X-Guzzle-Redirect-History');
        $path = $this->absolutePath($path);
        self::assertEquals($path, end($headers));
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
     * @return array|\SimpleXMLElement|\stdClass
     * @throws ContentTypeNotFoundException
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
     * @param $type
     * @param $path
     * @param $options
     */
    private function request($type, $path, $options)
    {
        $options = $options + [
                'allow_redirects' => [
                    'track_redirects' => true
                ]
            ];
        try {
            $this->response = $this->client->$type($path, $options);
            $this->statusCode = $this->response->getStatusCode();
        } catch (ClientException $e) {
            $this->response = $e->getResponse();
            $this->statusCode = $e->getResponse()->getStatusCode();
        } catch (ServerException $e) {
            $this->response = $e->getResponse();
            $this->statusCode = $e->getResponse()->getStatusCode();
        }
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

    /**
     * @param $path
     *
     * @return mixed
     */
    private function absolutePath($path)
    {
        $baseUrl = $this->baseUrl();

        if ( ! strstr($path, $baseUrl)) {
            $path = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }

        return $path;
    }

    /**
     * @return mixed
     */
    private function baseUrl()
    {
        return $_ENV['api_base_url'];
    }
}
