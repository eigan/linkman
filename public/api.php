<?php

require __DIR__ . "/../vendor/autoload.php";

use Linkman\Linkman;
use Opulence\Http\Requests\Request;

$linkman = new Linkman(__DIR__."/../");
$api = new \Linkman\Http\Kernel($linkman);
$api->start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Expose-Headers: Link, X-Total-Count');

$response = $api->handle(Request::createFromGlobals());

return $response->send();