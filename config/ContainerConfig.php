<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$container['config.cloudinary'] = [
    'cloud_name' => $_SERVER['CLOUDINARY_NAME'],
    'api_key'    => $_SERVER['CLOUDINARY_KEY'],
    'api_secret' => $_SERVER['CLOUDINARY_SECRET'],
];

$container['config.db'] = [
    'dbname'   => $_SERVER['DB_NAME'],
    'user'     => $_SERVER['DB_USER'],
    'password' => $_SERVER['DB_PASS'],
    'host'     => $_SERVER['DB_HOST'],
    'port'     => $_SERVER['DB_PORT'],
];

$container['config.hmac.secret'] = $_SERVER['TOKEN_KEY']; // for locking access tokens

$container['config.logger'] = function () {
    $logger       = new Logger('SURVEY54_' . $_SERVER['SERVICE_VERSION']);
    $file_handler = new StreamHandler('php://stderr');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['config.cint'] = [
    'key'      => $_SERVER['CINT_X_API_KEY'],
    'endpoint' => $_SERVER['CINT_ENDPOINT'],
    'fe'       => $_SERVER['FE_CLIENT'],
];

$container['config.swift'] = [
    'host' => $_SERVER['MAIL_HOST'],
    'port' => $_SERVER['MAIL_PORT'],
    'user' => $_SERVER['MAIL_USER'],
    'pass' => $_SERVER['MAIL_PASS'],
    'encr' => $_SERVER['MAIL_ENCRYPTION'],
];
