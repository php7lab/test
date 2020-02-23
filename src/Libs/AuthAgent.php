<?php

namespace PhpLab\Test\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Legacy\Yii\Helpers\FileHelper;
use PhpLab\Core\Libs\Env\EnvConfigException;
use PhpLab\Test\Helpers\RestHelper;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

class AuthAgent
{

    private $guzzleClient;
    private $authUri = 'auth';
    private $authCache;
    private $currentAuth = [];

    public function __construct(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
        if (empty($_ENV['CACHE_DIRECTORY'])) {
            throw new EnvConfigException('Empty env config for CACHE_DIRECTORY');
        }
        $cacheDirectory = FileHelper::path($_ENV['CACHE_DIRECTORY']);
        $this->authCache = new FilesystemAdapter('test', 0, $cacheDirectory);
    }

    public function authByLogin(string $login, string $password = 'Wwwqqq111')
    {
        $this->currentAuth = [
            'login' => $login,
            'password' => $password,
        ];
        return $this;
    }

    public function logout()
    {
        $this->currentAuth = [];
    }

    public function getAuthToken(): ?string
    {
        if (empty($this->currentAuth['login'])) {
            return null;
        }
        /** @var CacheItem $cacheItem */
        $cacheItem = $this->authCache->getItem('token_by_login_' . $this->currentAuth['login']);
        $authToken = $cacheItem->get();

        if ($authToken) {
            return $authToken;
        } else {
            return $this->authorization();
        }
    }

    public function authorization()
    {
        $options = [
            RequestOptions::FORM_PARAMS => [
                'login' => $this->currentAuth['login'],
                'password' => $this->currentAuth['password'],
            ],
        ];
        $response = $this->guzzleClient->request(HttpMethodEnum::POST, $this->authUri, $options);
        $authToken = RestHelper::getBodyAttribute($response, 'token');
        $this->setAuthToken($authToken);
        return $authToken;
    }

    protected function setAuthToken(string $authToken)
    {
        /** @var CacheItem $cacheItem */
        $cacheItem = $this->authCache->getItem('token_by_login_' . $this->currentAuth['login']);
        $cacheItem->set($authToken);
        $this->authCache->save($cacheItem);
        return $this;
    }

}
