<?php

namespace PhpLab\Test\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PhpLab\Core\Domain\Entities\DataProviderEntity;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Test\Helpers\RestHelper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RestAssert
{

    private $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function assertSubsetText(ResponseInterface $response, $actualString)
    {
        $body = RestHelper::getBody($response);
        //$body = StringHelper::removeAllSpace($body);
        $body = StringHelper::filterChar($body, '#[^а-яА-ЯёЁa-zA-Z]+#u');
        //$actualString = StringHelper::removeAllSpace($actualString);
        $actualString = StringHelper::filterChar($actualString, '#[^а-яА-ЯёЁa-zA-Z]+#u');
        $isFail = mb_strpos($body, $actualString) === false;
        if ($isFail) {
            $this->testCase->expectExceptionMessage('Subset string not found in text!');
        }
        $this->testCase->assertEquals(false, $isFail);
    }

    public function assertBody(ResponseInterface $response, $actualBody)
    {
        $body = RestHelper::getBody($response);
        $this->testCase->assertArraySubset($actualBody, $body);
    }

    public function assertCreated(ResponseInterface $response, $actualEntityId = null)
    {
        $this->testCase->assertEquals(HttpStatusCodeEnum::CREATED, $response->getStatusCode());
        $entityId = $response->getHeader(HttpHeaderEnum::X_ENTITY_ID)[0];
        $this->testCase->assertNotEmpty($entityId);
        if ($actualEntityId) {
            $this->testCase->assertEquals($actualEntityId, $entityId);
        }
    }

    public function assertCors(ResponseInterface $response, $origin, $headers = null, $methods = null)
    {
        $actualOrigin = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_ORIGIN)[0] ?? null;
        $actualHeaders = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_HEADERS)[0] ?? null;
        $actualMethods = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_METHODS)[0] ?? null;

        $this->testCase->assertEquals($origin, $actualOrigin);

        if ($headers) {
            $this->testCase->assertEquals($headers, $actualHeaders);
        }
        if ($methods) {
            $arr = explode(',', $actualMethods);
            $arr = array_map('trim', $arr);
            $diff = array_diff($methods, $arr);
            $this->testCase->assertEmpty($diff, 'Diff: ' . implode(',', $diff));
        }
    }

    public function assertOrder($collection, string $attribute, int $direction = SORT_ASC)
    {
        $currentValue = null;
        foreach ($collection as $item) {
            if ($currentValue === null) {
                $currentValue = ArrayHelper::getValue($item, $attribute);
            }
            if ($direction == SORT_ASC) {
                if (ArrayHelper::getValue($item, $attribute) < $currentValue) {
                    $this->testCase->expectExceptionMessage('Fail order!');
                }
                if (ArrayHelper::getValue($item, $attribute) > $currentValue) {
                    $currentValue = ArrayHelper::getValue($item, $attribute);
                }
            } else {
                if (ArrayHelper::getValue($item, $attribute) > $currentValue) {
                    $this->testCase->expectExceptionMessage('Fail order!');
                }
                if (ArrayHelper::getValue($item, $attribute) < $currentValue) {
                    $currentValue = ArrayHelper::getValue($item, $attribute);
                }
            }
        }
    }

    public function assertPagination(ResponseInterface $response, int $totalCount = null, int $page = null, int $pageSize = null)
    {
        $dataProviderEntity = RestHelper::forgeDataProviderEntity($response);
        if ($page) {
            $this->testCase->assertEquals($page, $dataProviderEntity->getPage());
        }
        if ($pageSize) {
            $this->testCase->assertEquals($pageSize, $dataProviderEntity->getPageSize());
        }
        if ($totalCount) {
            $this->testCase->assertEquals($totalCount, $dataProviderEntity->getTotalCount());
        }
        $this->testCase->assertEquals($dataProviderEntity->getPageCount(), $response->getHeader(HttpHeaderEnum::PAGE_COUNT)[0]);
    }

}
