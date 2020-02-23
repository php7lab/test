<?php

namespace PhpLab\Test\Helpers;

use PhpLab\Core\Domain\Entities\DataProviderEntity;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Core\Legacy\Yii\Helpers\FileHelper;
use PhpLab\Core\Libs\Store\Store;
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
        $body = RestHelper::getBody($response, $response->getBody()->getContents());
        return ArrayHelper::getValue($body, $name);
    }

    static public function getBody(ResponseInterface $response, string $body)
    {
        $contentType = self::extractHeaderValue($response, 'content-type');
        $extension = self::mimeToFileExtension($contentType);
        if($extension == 'php' || empty($extension)) {
            $extension = 'html';
        }
        $encoder = new Store($extension);
        $body = $encoder->decode($body);
        return $body;
    }

    static public function getLastInsertId(ResponseInterface $response): int
    {
        $entityId = $response->getHeader(HttpHeaderEnum::X_ENTITY_ID)[0];
        return intval($entityId);
    }

    static private function extractHeaderValue(ResponseInterface $response, string $name, int $part = 0)
    {
        $value = $response->getHeader($name)[0];
        $parts = explode(';', $value);
        $parts = array_map('trim', $parts);
        return strtolower($parts[$part]);
    }

    static private function mimeToFileExtension(string $contentType, string $default = 'html'): string {
        $mimeTypes = include FileHelper::path('vendor/php7lab/core/src/Legacy/Yii/Helpers/mimeTypes.php');
        $mimeTypes = array_flip($mimeTypes);
        $extension = ArrayHelper::getValue($mimeTypes, $contentType, $default);
        return strtolower($extension);
    }
}
