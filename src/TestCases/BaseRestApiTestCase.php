<?php

namespace Untek\Framework\RestApiTest\TestCases;

use Untek\Component\FormatAdapter\Store;
use Untek\Framework\WebTest\Libs\JsonImitationRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseRestApiTestCase extends TestCase
{
    protected function getRequestImitator(): JsonImitationRequest
    {
        return new JsonImitationRequest($this->endpointScript, $this->baseUrl);
    }

    protected function sendRequest(?string $uri = null, string $method = 'GET', array $data = []): Response
    {
        return $this->getRequestImitator()->sendJsonRequest($uri, $method, $data);
    }

    /**
     * @param Response $response
     * @param string|null $format
     * @deprecated
     * @see printResponseData
     */
    protected function printResponceData(Response $response, ?string $format = 'php') {
        $this->printResponseData($response, $format);
    }

    protected function printResponseData(Response $response, ?string $format = 'php') {
        $data = $this->extractData($response);
        if($format == 'php') {
            $data = (new Store('php'))->encode($data);
        } elseif($format == 'json') {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } elseif($format == null) {
            $data = print_r($data, true);
        }
        echo $data;
        echo PHP_EOL;
        exit();
    }

    protected function extractData(Response $response) {
        if($response->headers->get('Content-Type') == 'application/json') {
            return json_decode($response->getContent(), true);
        }
        return $response->getContent();
    }

    protected function extractHeaders(Response $response) {
        $headers = [];
        foreach ($response->headers->allPreserveCase() as $name => $value) {
            $headers[$name] = $value[0];
        }
        return $headers;
    }
}
