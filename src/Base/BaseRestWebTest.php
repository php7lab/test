<?php

namespace PhpLab\Test\Base;

use PhpLab\Test\Asserts\RestWebAssert;
use Psr\Http\Message\ResponseInterface;

abstract class BaseRestWebTest extends BaseRestTest
{

    protected function printHtmlContent(ResponseInterface $response = null) {
        $this->printContent($response, 'strip_tags');
    }

    protected function printContent(ResponseInterface $response = null, string $filter = null) {
        $content = $response->getBody()->getContents();
        if($filter) {
            $content = $filter($content);
        }
        dd($content);
    }

    protected function getRestAssert(ResponseInterface $response = null): RestWebAssert
    {
        return new RestWebAssert($response);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setBaseUrl($_ENV['WEB_URL']);
    }
}