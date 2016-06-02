<?php

require 'vendor/autoload.php';

$action = function ($request, $response) {
  $response->getBody()->write("Hello world!");
};

Zend\Diactoros\Server::createServerfromRequest($action, Zend\Diactoros\ServerRequestFactory::fromGlobals())->listen();
