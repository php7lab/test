<?php

namespace PhpLab\Test\Base;

use PhpLab\Core\Domain\Exceptions\UnprocessibleEntityException;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Test\Libs\FixtureLoader;
use PHPUnit\Framework\TestCase;

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
        if ($fixtures) {
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