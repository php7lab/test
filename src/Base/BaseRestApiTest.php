<?php

namespace PhpLab\Test\Base;

use PhpLab\Test\Asserts\RestApiAssert;
use Psr\Http\Message\ResponseInterface;

abstract class BaseRestApiTest extends BaseRestTest
{

    protected function getRestAssert(ResponseInterface $response = null): RestApiAssert
    {
        return new RestApiAssert($response);
    }

}