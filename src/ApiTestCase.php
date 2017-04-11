<?php

declare(strict_types=1);

namespace Brunty;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;

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

    /**
     * @var \SimpleXMLElement
     */
    private $bodyAsXml;

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

    public function client(): Client
    {
        return $this->client;
    }

    public function response(): GuzzleResponse
    {
        return $this->response;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function get(string $path, array $options = [])
    {
        $this->request('get', $path, $options);
    }

    public function post(string $path, array $options = [])
    {
        $this->request('post', $path, $options);
    }

    public function patch(string $path, array $options = [])
    {
        $this->request('patch', $path, $options);
    }

    public function put(string $path, array $options = [])
    {
        $this->request('put', $path, $options);
    }

    public function delete(string $path, array $options = [])
    {
        $this->request('delete', $path, $options);
    }

    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }

    public function assertResponseStatus(int $status)
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

    public function assertRedirectedTo(string $path)
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

    public function assertResponseHasKey(string $key)
    {
        $content = $this->responseBody(true);

        self::assertTrue(isset($content[$key]), sprintf('Response body does not have the key %s', $key));
    }

    /**
     * @param string      $query
     * @param string|null $value
     */
    public function assertNodeIsValue(string $query, $value)
    {
        self::assertEquals($value, $this->query($query));
    }

    public function rawResponseBody(): string
    {
        return $this->response->getBody()->getContents();
    }

    /**
     * @param bool $asArray
     *
     * @return array|\SimpleXMLElement|\stdClass
     * @throws ContentTypeNotFoundException
     */
    public function responseBody(bool $asArray = false)
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

    public function getContentType(): string
    {
        return $this->response->getHeader('Content-Type')[0];
    }

    public function contentTypeIsXml(): bool
    {
        return strpos($this->getContentType(), self::XML_CONTENT_TYPE) !== false;
    }

    public function contentTypeIsJson(): bool
    {
        return strpos($this->getContentType(), self::JSON_CONTENT_TYPE) !== false;
    }

    private function request(string $type, string $path, array $options = [])
    {
        $options += [
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

    private function absolutePath(string $path): string
    {
        $baseUrl = $this->baseUrl();

        if (strpos($path, $baseUrl) === false) {
            $path = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }

        return $path;
    }

    private function baseUrl(): string
    {
        return $_ENV['api_base_url'];
    }

    private function getBodyAsXml(): SimpleXMLElement
    {
        if ($this->bodyAsXml === null) {
            if ($this->contentTypeIsXml()) {
                $this->bodyAsXml = $this->responseBody();
            }

            if ($this->contentTypeIsJson()) {
                $this->bodyAsXml = new \SimpleXMLElement(ArrayToXml::convert($this->responseBody(true)));
            }
        }

        return $this->bodyAsXml;
    }

    /**
     * @param string $query
     *
     * @return null|string
     */
    private function query(string $query)
    {
        $xml = $this->getBodyAsXml();

        $result = $xml->xpath($query);

        if (count($result)) {
            return (string) $result[0];
        }

        return null;
    }
}
