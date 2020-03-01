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
        foreach ($fixtures as $fixture) {
            $this->loadFixture($fixture);
        }
    }

    private function loadFixture(string $fixture)
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
