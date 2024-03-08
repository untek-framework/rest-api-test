<?php

namespace Untek\Framework\RestApiTest\TestCases;

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

    protected function printResponceData(Response $response) {
        $data = $this->extractData($response);
        dd($data);
    }

    protected function extractData(Response $response) {
        return json_decode($response->getContent(), true);
    }

    protected function extractHeaders(Response $response) {
        $headers = [];
        foreach ($response->headers->allPreserveCase() as $name => $value) {
            $headers[$name] = $value[0];
        }
        return $headers;
    }
}
