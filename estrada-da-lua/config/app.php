<?php

define('APP_NAME', 'Estrada da Lua');
define('APP_ROOT', dirname(__DIR__));

/*
 * ================================================================
 * LOGO DA ESTRADA DA LUA
 * ================================================================
 * Para trocar a logo do site inteiro, substitua apenas o arquivo:
 * /img/logo-estrada-da-lua.png
 *
 * O restante do código usa esta constante automaticamente.
 */
define('LOGO_PATH', '/img/logo-estrada-da-lua.png');

/*
 * BASE_URL é detectada automaticamente a partir da pasta do projeto.
 * Assim o site continua funcionando mesmo se a pasta for renomeada.
 */
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot = realpath(APP_ROOT);
$baseUrl = '/estrada-da-lua';

if ($documentRoot && $projectRoot && strpos($projectRoot, $documentRoot) === 0) {
    $relative = substr($projectRoot, strlen($documentRoot));
    $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    $baseUrl = $relative === '' ? '' : '/' . ltrim($relative, '/');
}

define('BASE_URL', rtrim($baseUrl, '/'));
