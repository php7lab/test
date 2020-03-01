<?php

namespace PhpLab\Test\Base;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PhpLab\Core\Helpers\ClassHelper;
use PhpLab\Core\Helpers\InstanceHelper;
use PhpLab\Rest\Contract\Authorization\AuthorizationInterface;
use PhpLab\Rest\Contract\Authorization\BearerAuthorization;
use PhpLab\Test\Asserts\RestApiAssert;
use PhpLab\Rest\Contract\Client\RestClient;
use Psr\Http\Message\ResponseInterface;

abstract class BaseRestApiTest extends BaseRestTest
{

    protected function getRestAssert(ResponseInterface $response = null): RestApiAssert
    {
        return new RestApiAssert($response);
    }

}