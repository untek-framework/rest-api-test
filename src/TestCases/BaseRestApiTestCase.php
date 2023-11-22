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
        $data = json_decode($response->getContent(), true);
        dd($data);
    }
}
