<?php

namespace Untek\Framework\RestApiTest\Asserts;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpFoundation\Response;
use Untek\Core\Arr\Helpers\ArrayHelper;
use Untek\Framework\Rpc\Domain\Enums\RpcErrorCodeEnum;
use Untek\Component\Http\Enums\HttpStatusCodeEnum;

class RestApiResponseAssert extends Assert
{

    protected Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    protected function getPayload(): array {
        return json_decode($this->response->getContent(), JSON_OBJECT_AS_ARRAY);
    }

    public function getValueFromPath(string $path = null) {
        $responseBody = $this->getPayload();
        if($path) {
            $actual = ArrayHelper::getValue($responseBody, $path);
        } else {
            $actual = $responseBody;
        }
        return $actual;
    }

    /*protected function dumpPayload(Response $response)
    {
        $responseBody = ;
        dd($responseBody);
    }*/

    public function assertHeader($expected, string $name): static
    {
        $actual = $this->response->headers->get($name);
//        dd($name, $actual);
        $this->assertEquals($expected, $actual);
        return $this;
    }

    public function assertPathWithRegexp($exp, string $path = null): static
    {
        $actual = $this->getValueFromPath($path);
        if(!preg_match("/$exp/u", $actual)) {
            throw new ExpectationFailedException('Not valid value!');
        }
        return $this;
    }

    public function assertPath($expected, string $path = null): static
    {
        $actual = $this->getValueFromPath($path);
        $this->assertEquals($expected, $actual);
        return $this;
    }

    public function assertHasPath(string $path): static
    {
        $responseBody = $this->getPayload();
        $has = ArrayHelper::has($responseBody, $path);
        if(!$has) {
            throw new ExpectationFailedException("Path \"{$path}\" not found.");
        }
        return $actual;
    }

    public function assertData(array $data): static
    {
        $responseBody = $this->getPayload();
        $this->assertEquals($data, $responseBody);
        return $this;
    }


    public function assertNotFound(string $message = null): static
    {
        $this->assertError(HttpStatusCodeEnum::NOT_FOUND, $message);
        return $this;
    }

    public function assertForbidden(string $message = null): static
    {
        $this->assertError(HttpStatusCodeEnum::FORBIDDEN, $message);
        return $this;
    }

    public function assertUnauthorized(string $message = null): static
    {
        $this->assertError(HttpStatusCodeEnum::UNAUTHORIZED, $message);
        return $this;
    }

    public function assertError(int $code, string $message = null): static
    {
        $this->assertStatus($code);
        if ($message) {
            $this->assertErrorMessage($message);
        }
        return $this;
    }

    public function assertStatus(int $code): static
    {
//        $this->assertIsError();
        $this->assertEquals($code, $this->response->getStatusCode());
        return $this;
    }

    public function assertErrorMessage(string $message): static
    {
//        $this->assertIsError();
        $this->assertPath($message, 'message');
//        $this->assertEquals($message, $this->response->getError()[]);
        return $this;
    }

    public function assertIsError(string $message = 'Response is not error'): static
    {
        $this->assertTrue($this->response->isError(), $message);
        return $this;
    }

    public function assertIsResult(string $message = 'Response is not success'): static
    {
        $this->assertTrue($this->response->isSuccess(), $message);
        return $this;
    }

    public function assertId($expected): static
    {
        $this->assertEquals($expected, $this->response->getId());
        return $this;
    }

    public function assertResult($expectedResult): static
    {
        $this->assertIsResult();
        if (is_array($expectedResult)) {
            $this->assertArraySubset($expectedResult, $this->response->getResult());
        } else {
            $this->assertEquals($expectedResult, $this->response->getResult());
        }
        return $this;
    }

    public function assertCollectionSize(int $expected): static
    {
        $this->assertCount($expected, $this->getPayload());
//        $totalCount = $this->response->getMetaItem('totalCount', null);
//        if ($totalCount !== null) {
//            $this->assertEquals($expected, $totalCount);
//        }
        return $this;
    }

    public function assertCollectionSizeByPath(int $expected, string $path): static
    {
        $data = ArrayHelper::getValue($this->response->getResult(), $path);
        $this->assertCount($expected, $data);
        /*$totalCount = $this->response->getMetaItem('totalCount', null);
        if($totalCount !== null) {
            $this->assertEquals($expected, $totalCount);
        }*/
        return $this;
    }

    public function assertCollection($data): static
    {
        $this->assertResult($data);
        $this->assertCollectionSize(count($data));
        return $this;
    }

    public function assertCollectionIsEmpty(): static
    {
        $this->assertIsResult();
        $this->assertCollectionSize(0);
        return $this;
    }

    public function assertCollectionItemsById(array $ids): static
    {
        $this->assertIsResult();
        $this->assertCollectionSize(count($ids));

        $actualIds = ArrayHelper::getColumn($this->response->getResult(), 'id');
        sort($ids);
        sort($actualIds);
        $this->assertEquals($ids, $actualIds);
        return $this;
    }

    public function assertCollectionItemsByAttribute(array $values, string $attributeName): static
    {
        $this->assertIsResult();
        $this->assertCollectionSize(count($values));

        $collection = $this->response->getResult();
        $this->assertItemsByAttribute($values, $attributeName, $collection);
        return $this;
    }

    private function assertCollectionCount(int $expected): static
    {
        $this->assertIsResult();
        $this->assertCount($expected, $this->response->getResult());
        $this->assertEquals($expected, $this->response->getMetaItem('perPage'));
//        $this->assertEquals($expected, $this->response->getMetaItem('totalCount'));
        return $this;
    }

    public function assertPagination(int $totalCount = null, int $count, int $pageSize = null, int $page = 1): static
    {
        if ($totalCount !== null) {
            $this->assertEquals($totalCount, $this->response->getMetaItem('totalCount'));
        }
//        if($count) {
        $this->assertCollectionCount($count);
//        }
        if ($pageSize !== null) {
            $this->assertEquals($pageSize, $this->response->getMetaItem('perPage'));
        }

        $this->assertEquals($page, $this->response->getMetaItem('page'));
        return $this;
    }

    public function assertUnprocessableEntity(array $fieldNames = []): static
    {
        $this->assertIsError();
        $this->assertErrorMessage('Parameter validation error');
        $this->assertStatus(RpcErrorCodeEnum::SERVER_ERROR_INVALID_PARAMS);
        if ($fieldNames) {
            foreach ($this->response->getError()['data'] as $item) {
                if (empty($item['field']) || empty($item['message'])) {
                    $this->expectExceptionMessage('Invalid errors array!');
                }
                $expectedBody[] = $item['field'];
            }
            $this->assertEquals($fieldNames, $expectedBody);
        }
        return $this;
    }

    public function assertUnprocessableEntityErrors(array $errors): static
    {
        $this->assertIsError();
        $this->assertErrorMessage('Parameter validation error');
        $this->assertStatus(RpcErrorCodeEnum::SERVER_ERROR_INVALID_PARAMS);
        $this->assertEquals(
            $this->unprocessableEntityErrorsToFlat($errors),
            $this->unprocessableEntityErrorsToFlat($this->response->getError()['data'])
        );
        return $this;
    }

    protected function unprocessableEntityErrorsToFlat(array $errors)
    {
        $flat = [];
        foreach ($errors as $error) {
            $flat[] = "{$error['field']}|{$error['message']}";
        }
        sort($flat);
        return $flat;
    }
}
