<?php

namespace PhpLab\Test\Libs\FixtureLoader;

use PhpLab\Core\Helpers\InstanceHelper;
use yii\test\Fixture;

class YiiFixtureLoader implements FixtureLoaderInterface
{

    protected $loadedFixtures = [];

    public function __construct()
    {
        $this->loadedFixtures = [];
    }

    public function loadFixtures(array $fixtures)
    {
        if (empty($fixtures)) {
            return;
        }
        foreach ($fixtures as $fixtureClassName) {
            $this->loadFixture($fixtureClassName);
        }
    }

    private function loadFixture(string $fixtureClassName)
    {
        if (isset($this->loadedFixtures[$fixtureClassName])) {
            return;
        }
        $this->loadedFixtures[$fixtureClassName] = true;
        /** @var Fixture $fixtureInstance */
        $fixtureInstance = InstanceHelper::ensure($fixtureClassName);
        if ($fixtureInstance->depends) {
            $this->loadFixtures($fixtureInstance->depends);
        }
        $fixtureInstance->unload();
        $fixtureInstance->load();
    }

}
