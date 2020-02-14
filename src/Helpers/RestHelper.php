<?php

namespace PhpLab\Test\Helpers;

use PhpLab\Core\Domain\Entities\DataProviderEntity;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use Psr\Http\Message\ResponseInterface;

class RestHelper
{

    static public function forgeDataProviderEntity(ResponseInterface $response): DataProviderEntity
    {
        $entity = new DataProviderEntity;
        $entity->setPageSize($response->getHeader(HttpHeaderEnum::PER_PAGE)[0]);
        $entity->setPage($response->getHeader(HttpHeaderEnum::CURRENT_PAGE)[0]);
        $entity->setTotalCount($response->getHeader(HttpHeaderEnum::TOTAL_COUNT)[0]);
        //$entity->pageCount = $response->getHeader(HttpHeaderEnum::PAGE_COUNT)[0];
        return $entity;
    }

    static public function getBodyAttribute(ResponseInterface $response, $name)
    {
        $body = self::getBody($response);
        return ArrayHelper::getValue($body, $name);
    }

    static public function getBody(ResponseInterface $response)
    {
        $contentType = self::extractHeaderValue($response, 'content-type');
        $body = $response->getBody()->getContents();
        if ($contentType == 'application/json') {
            $body = \GuzzleHttp\json_decode($response->getBody(), true);
        }
        return $body;
    }

    static public function getLastInsertId(ResponseInterface $response)
    {
        $entityId = $response->getHeader(HttpHeaderEnum::X_ENTITY_ID)[0];
        return $entityId;
    }

    static private function extractHeaderValue(ResponseInterface $response, string $name, int $part = 0) {
        $value = $response->getHeader($name)[0];
        $parts = explode(';', $value);
        $parts = array_map('trim', $parts);
        return $parts[$part];
    }

}
