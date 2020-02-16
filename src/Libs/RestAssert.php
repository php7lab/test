<?php

namespace PhpLab\Test\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use PhpLab\Core\Domain\Entities\DataProviderEntity;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Enums\Http\HttpStatusCodeEnum;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Test\Helpers\RestHelper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RestAssert extends TestCase
{

    private $response;

    public function __construct(ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    public function assertUnprocessableEntity(array $fieldNames = [])
    {
        if($fieldNames) {
            $body = RestHelper::getBody($this->response);
            foreach ($body as $item) {
                if(empty($item['field']) || empty($item['message'])) {
                    $this->expectExceptionMessage('Invalid errors array!');
                }
                $expectedBody[] = $item['field'];
            }
            $this->assertEquals($fieldNames, $expectedBody);
        }
        $this->assertStatusCode(HttpStatusCodeEnum::UNPROCESSABLE_ENTITY);
        return $this;
    }
    
    public function assertSubsetText($actualString, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        $body = RestHelper::getBody($response);
        //$body = StringHelper::removeAllSpace($body);
        $body = StringHelper::filterChar($body, '#[^а-яА-ЯёЁa-zA-Z]+#u');
        //$actualString = StringHelper::removeAllSpace($actualString);
        $actualString = StringHelper::filterChar($actualString, '#[^а-яА-ЯёЁa-zA-Z]+#u');
        $isFail = mb_strpos($body, $actualString) === false;
        if ($isFail) {
            $this->expectExceptionMessage('Subset string not found in text!');
        }
        $this->assertEquals(false, $isFail);
        return $this;
    }

    public function assertStatusCode(int $actualStatus = null, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        $statusCode = $response->getStatusCode();
        if($actualStatus) {
            $this->assertEquals($actualStatus, $statusCode);
        } else {
            $this->assertTrue($statusCode < 300 && $statusCode >= 200);
        }
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

    public function assertBody($expectedBody, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        $body = RestHelper::getBody($response);
        $this->assertArraySubset($expectedBody, $body);
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
        $collection = RestHelper::getBody($response);
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
        $dataProviderEntity = RestHelper::forgeDataProviderEntity($response);
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

}
