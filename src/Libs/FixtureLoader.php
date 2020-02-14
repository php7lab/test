<?php

namespace PhpLab\Test\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PhpLab\Core\Helpers\InstanceHelper;
use PhpLab\Core\Helpers\StringHelper;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Enums\Http\HttpStatusCodeEnum;
use PhpLab\Test\Helpers\RestHelper;
use PhpLab\Test\Libs\RestAssert;
use PhpLab\Test\Libs\RestClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use yii\test\Fixture;

class FixtureLoader
{

    protected $loadedFixtures = [];

    public function load(array $fixtures)
    {
        $this->loadedFixtures = [];
        $this->loadFixtures($fixtures);
    }

    protected function loadFixtures(array $fixtures)
    {
        if ($fixtures) {
            foreach ($fixtures as $fixture) {
                $this->loadFixture($fixture);
            }
        }
    }

    protected function loadFixture($fixture)
    {
        if (isset($this->loadedFixtures[$fixture])) {
            return;
        }
        /** @var Fixture $fixtureInstance */
        $fixtureInstance = InstanceHelper::ensure($fixture);
        if ($fixtureInstance->depends) {
            $this->loadFixtures($fixtureInstance->depends);
        }
        $fixtureInstance->unload();
        $fixtureInstance->load();
        $this->loadedFixtures[$fixture] = true;
    }
}
