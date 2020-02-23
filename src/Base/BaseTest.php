<?php

namespace PhpLab\Test\Base;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PhpLab\Core\Domain\Exceptions\UnprocessibleEntityException;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Helpers\InstanceHelper;
use PhpLab\Core\Helpers\StringHelper;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Enums\Http\HttpStatusCodeEnum;
use PhpLab\Test\Helpers\RestHelper;
use PhpLab\Test\Libs\FixtureLoader;
use PhpLab\Test\Libs\RestAssert;
use PhpLab\Test\Libs\RestClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use yii\test\Fixture;

abstract class BaseTest extends TestCase
{

    protected function fixtures(): array
    {
        return [];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $fixtures = $this->fixtures();
        if($fixtures) {
            $fixtureLoader = new FixtureLoader;
            $fixtureLoader->load($fixtures);
        }
    }

    protected function assertUnprocessibleEntityException($expected, UnprocessibleEntityException $e, bool $debug = false)
    {
        $errorCollection = $e->getErrorCollection();
        $arr = EntityHelper::collectionToArray($errorCollection);
        $this->assertArraySubset($expected, $arr);
    }

}