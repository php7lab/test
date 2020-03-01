<?php

namespace PhpLab\Test\Asserts;

use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpStatusCodeEnum;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Rest\Helpers\RestResponseHelper;
use PhpLab\Test\Helpers\RestHelper;
use Psr\Http\Message\ResponseInterface;

class RestApiAssert extends RestAssert
{

    public function __construct(ResponseInterface $response = null)
    {
        parent::__construct($response);
        $this->body = RestResponseHelper::getBody(clone $this->response, $this->rawBody);
    }

    public function assertUnprocessableEntity(array $fieldNames = [])
    {
        if ($fieldNames) {
            foreach ($this->body as $item) {
                if (empty($item['field']) || empty($item['message'])) {
                    $this->expectExceptionMessage('Invalid errors array!');
                }
                $expectedBody[] = $item['field'];
            }
            $this->assertEquals($fieldNames, $expectedBody);
        }
        $this->assertStatusCode(HttpStatusCodeEnum::UNPROCESSABLE_ENTITY);
        return $this;
    }

    public function assertCollection($expectedBody, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        //$this->assertStatusCode(HttpStatusCodeEnum::OK, $response);
        //$this->assertPagination($response, null, 1, 20);
        $this->assertBody($response, $expectedBody);
        return $this;
    }

    public function assertCreated($actualEntityId = null, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        $this->assertEquals(HttpStatusCodeEnum::CREATED, $response->getStatusCode());
        $entityId = $response->getHeader(HttpHeaderEnum::X_ENTITY_ID)[0];
        $this->assertNotEmpty($entityId);
        if ($actualEntityId) {
            $this->assertEquals($actualEntityId, $entityId);
        }
        return $this;
    }

    public function assertCors($origin, $headers = null, $methods = null, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        $actualOrigin = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_ORIGIN)[0] ?? null;
        $actualHeaders = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_HEADERS)[0] ?? null;
        $actualMethods = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_METHODS)[0] ?? null;

        $this->assertEquals($origin, $actualOrigin);

        if ($headers) {
            $this->assertEquals($headers, $actualHeaders);
        }
        if ($methods) {
            $arr = explode(',', $actualMethods);
            $arr = array_map('trim', $arr);
            $diff = array_diff($methods, $arr);
            $this->assertEmpty($diff, 'Diff: ' . implode(',', $diff));
        }
        return $this;
    }

    public function assertOrder(string $attribute, int $direction = SORT_ASC, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        $collection = $this->body;
        $currentValue = null;
        foreach ($collection as $item) {
            if ($currentValue === null) {
                $currentValue = ArrayHelper::getValue($item, $attribute);
            }
            if ($direction == SORT_ASC) {
                if (ArrayHelper::getValue($item, $attribute) < $currentValue) {
                    $this->expectExceptionMessage('Fail order!');
                }
                if (ArrayHelper::getValue($item, $attribute) > $currentValue) {
                    $currentValue = ArrayHelper::getValue($item, $attribute);
                }
            } else {
                if (ArrayHelper::getValue($item, $attribute) > $currentValue) {
                    $this->expectExceptionMessage('Fail order!');
                }
                if (ArrayHelper::getValue($item, $attribute) < $currentValue) {
                    $currentValue = ArrayHelper::getValue($item, $attribute);
                }
            }
        }
        return $this;
    }

    public function assertPagination(int $totalCount = null, int $page = null, int $pageSize = null, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        $dataProviderEntity = RestResponseHelper::forgeDataProviderEntity($response);
        if ($page) {
            $this->assertEquals($page, $dataProviderEntity->getPage());
        }
        if ($pageSize) {
            $this->assertEquals($pageSize, $dataProviderEntity->getPageSize());
        }
        if ($totalCount) {
            $this->assertEquals($totalCount, $dataProviderEntity->getTotalCount());
        }
        $this->assertEquals($dataProviderEntity->getPageCount(), $response->getHeader(HttpHeaderEnum::PAGE_COUNT)[0]);
        return $this;
    }

    public function assertBody($expectedBody, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        $this->assertArraySubset($expectedBody, $this->body);
        return $this;
    }

}
