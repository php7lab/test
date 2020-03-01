<?php

namespace PhpLab\Test\Asserts;

use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpStatusCodeEnum;
use PhpLab\Core\Helpers\StringHelper;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Test\Helpers\RestHelper;
use PhpLab\Rest\Helpers\RestResponseHelper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RestAssert extends TestCase
{

    protected $response;
    protected $rawBody;
    protected $body;

    public function __construct(ResponseInterface $response = null)
    {
        $this->response = $response;
        $this->rawBody = $response->getBody()->getContents();
        $this->body = RestResponseHelper::getBody(clone $this->response, $this->rawBody);
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
