<?php

namespace PhpLab\Test\Libs;

use PhpLab\Core\Helpers\InstanceHelper;
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
