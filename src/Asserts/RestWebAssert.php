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

class RestWebAssert extends RestAssert
{

    public function assertSubsetText($actualString, ResponseInterface $response = null)
    {
        $response = $response ?? $this->response;
        //$body = StringHelper::removeAllSpace($body);
        $exp = '#[^а-яА-ЯёЁa-zA-Z]+#u';
        $body = StringHelper::filterChar($this->rawBody, $exp);
        //$actualString = StringHelper::removeAllSpace($actualString);
        $actualString = StringHelper::filterChar($actualString, $exp);
        $isFail = mb_strpos($body, $actualString) === false;
        if ($isFail) {
            $this->expectExceptionMessage('Subset string not found in text!');
        }
        $this->assertEquals(false, $isFail);
        return $this;
    }

}
