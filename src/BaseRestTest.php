<?php

namespace PhpLab\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Core\Common\Helpers\StringHelper;
use PhpLab\Core\Domain\Data\DataProviderEntity;
use PhpLab\Core\Web\Enums\HttpHeaderEnum;
use PhpLab\Core\Web\Enums\HttpMethodEnum;
use PhpLab\Core\Web\Enums\HttpStatusCodeEnum;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class BaseRestTest extends TestCase
{

    protected $baseUrl;
    protected $basePath = '/';

    protected function setUp(): void
    {
        $this->baseUrl = $_ENV['API_DOMAIN_URL'];
    }

    protected function sendOptions(string $uri): ResponseInterface
    {
        return $this->sendRequest(HttpMethodEnum::OPTIONS, $uri);
    }

    protected function sendDelete(string $uri): ResponseInterface
    {
        return $this->sendRequest(HttpMethodEnum::DELETE, $uri);
    }

    protected function sendPost(string $uri, array $body = [], string $paramName = RequestOptions::FORM_PARAMS): ResponseInterface
    {
        $options = [$paramName => $body];
        return $this->sendRequest(HttpMethodEnum::POST, $uri, $options);
    }

    protected function sendPut(string $uri, array $body = [], string $paramName = RequestOptions::FORM_PARAMS): ResponseInterface
    {
        $options = [$paramName => $body];
        return $this->sendRequest(HttpMethodEnum::PUT, $uri, $options);
    }

    protected function sendGet(string $uri, array $query = [], string $paramName = RequestOptions::QUERY): ResponseInterface
    {
        $options = [$paramName => $query];
        return $this->sendRequest(HttpMethodEnum::GET, $uri, $options);
    }

    protected function assertSubsetText(ResponseInterface $response, $actualString)
    {
        $body = $this->getBody($response);
        //$body = StringHelper::removeAllSpace($body);
        $body = StringHelper::filterChar($body, '#[^а-яА-ЯёЁa-zA-Z]+#u');
        //$actualString = StringHelper::removeAllSpace($actualString);
        $actualString = StringHelper::filterChar($actualString, '#[^а-яА-ЯёЁa-zA-Z]+#u');
        $isFail = mb_strpos($body, $actualString) === false;
        if ($isFail) {
            $this->expectExceptionMessage('Subset string not found in text!');
        }
        $this->assertEquals(false, $isFail);
    }

    protected function assertBody(ResponseInterface $response, $actualBody)
    {
        $body = $this->getBody($response);
        $this->assertArraySubset($actualBody, $body);
    }

    protected function getLastInsertId(ResponseInterface $response)
    {
        $entityId = $response->getHeader(HttpHeaderEnum::X_ENTITY_ID)[0];
        return $entityId;
    }

    protected function assertCreated(ResponseInterface $response, $actualEntityId = null)
    {
        $this->assertEquals(HttpStatusCodeEnum::CREATED, $response->getStatusCode());
        $entityId = $response->getHeader(HttpHeaderEnum::X_ENTITY_ID)[0];
        $this->assertNotEmpty($entityId);
        if ($actualEntityId) {
            $this->assertEquals($actualEntityId, $entityId);
        }
    }

    protected function assertCors(ResponseInterface $response, $origin, $headers = null, $methods = null)
    {
        $actualOrigin = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_ORIGIN)[0];
        $actualHeaders = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_HEADERS)[0];
        $actualMethods = $response->getHeader(HttpHeaderEnum::ACCESS_CONTROL_ALLOW_METHODS)[0];

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
    }

    protected function assertOrder($collection, string $attribute, int $direction = SORT_ASC)
    {
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
    }

    protected function assertPagination(ResponseInterface $response, int $totalCount = null, int $page = null, int $pageSize = null)
    {
        $entity = new DataProviderEntity;

        $entity->setPageSize($response->getHeader(HttpHeaderEnum::PER_PAGE)[0]);
        $entity->setPage($response->getHeader(HttpHeaderEnum::CURRENT_PAGE)[0]);
        $entity->setTotalCount($response->getHeader(HttpHeaderEnum::TOTAL_COUNT)[0]);

        //$entity->pageCount = $response->getHeader(HttpHeaderEnum::PAGE_COUNT)[0];

        if ($page) {
            $this->assertEquals($page, $entity->getPage());
        }
        if ($pageSize) {
            $this->assertEquals($pageSize, $entity->getPageSize());
        }
        if ($totalCount) {
            $this->assertEquals($totalCount, $entity->getTotalCount());
        }
        $this->assertEquals($entity->getPageCount(), $response->getHeader(HttpHeaderEnum::PAGE_COUNT)[0]);
    }

    protected function getBody(ResponseInterface $response)
    {
        $contentType = $response->getHeader('content-type')[0];
        $body = $response->getBody()->getContents();
        if ($contentType == 'application/json') {
            $body = \GuzzleHttp\json_decode($response->getBody(), true);
        }
        return $body;
    }

    protected function sendRequest(string $method, string $uri = '', array $options = []): ResponseInterface
    {
        $client = $this->getGuzzleClient();
        try {
            $response = $client->request($method, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return $response;
    }

    protected function getGuzzleClient(): Client
    {
        $baseUrl = $this->getBaseUrl();
        $client = new Client([
            'base_uri' => $baseUrl . '/',
        ]);
        return $client;
    }

    private function getBaseUrl(): string
    {
        $baseUrl = $this->baseUrl . '/' . $this->basePath;
        $baseUrl = trim($baseUrl, '/');
        return $baseUrl;
    }

}