<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$app = require_once __DIR__.'/../app/bootstrap.php';



$request = Request::createFromGlobals();
$app->run($request);
