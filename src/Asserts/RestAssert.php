<?php

namespace PhpLab\Test\Asserts;

use PhpLab\Test\Helpers\RestHelper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class RestAssert extends TestCase
{

    protected $response;
    protected $rawBody;
    protected $body;

    public function __construct(ResponseInterface $response = null)
    {
        $this->response = $response;
        $this->rawBody = $response->getBody()->getContents();
    }

    public function getRawBody()
    {
        return $this->rawBody;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function assertStatusCode(int $actualStatus, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        $statusCode = $response->getStatusCode();
        $this->assertEquals($actualStatus, $statusCode);
        return $this;
    }

}
