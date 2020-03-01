<?php

namespace PhpLab\Test\Base;

use PhpLab\Test\Asserts\RestWebAssert;
use Psr\Http\Message\ResponseInterface;

abstract class BaseRestWebTest extends BaseRestTest
{

    protected function getRestAssert(ResponseInterface $response = null): RestWebAssert
    {
        return new RestWebAssert($response);
    }

}