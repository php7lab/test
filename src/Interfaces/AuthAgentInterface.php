<?php

namespace PhpLab\Test\Interfaces;

interface AuthAgentInterface
{

    public function authByLogin(string $login, string $password = 'Wwwqqq111'): AuthAgentInterface;

    public function logout(): AuthAgentInterface;

    public function getAuthToken(): ?string;

    public function authorization();

}