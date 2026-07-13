<?php
/***********************************************************************
 *          @Project    : WT FrameWork
 *          @version    : 2.0
 *          @author     : Mogbil Sourketti info[@]wondtech.com
 *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 *          @link       : http://www.wondtech.com
 *          @package    : WT FrameWork (2.0) — Improved
 *
 ************************************************************************/

namespace WT;

use WT\LIBS\Wt_Front;

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once 'wt' . DS . 'libs' . DS . 'wt_env.php';
$envPath = file_exists(__DIR__ . '/.env')
    ? __DIR__
    : dirname(__DIR__);
wt_loadEnv($envPath);
date_default_timezone_set($_ENV['APP_TZ'] ?? 'Asia/Riyadh');
$wtDebug = filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $wtDebug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting($wtDebug ? E_ALL & ~E_DEPRECATED : E_ALL & ~E_DEPRECATED & ~E_NOTICE);

$wtHttps = (($_SERVER['HTTPS'] ?? '') === 'on') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
if ($wtHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
header('Cache-Control: no-cache, must-revalidate');

set_exception_handler(function (\Throwable $e) use ($wtDebug) {
    error_log('[' . ($_ENV['APP_NAME'] ?? 'WT') . '] Uncaught: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (headers_sent()) return;
    http_response_code(500);
    $path  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $isApi = str_starts_with($path, '/api');
    $msg   = $wtDebug ? $e->getMessage() : 'Server error';
    if ($isApi) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['state' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        echo $msg;
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (($_SERVER['HTTPS'] ?? '') === 'on') || (($_SERVER['SERVER_PORT'] ?? '') == 443),
    ]);
    session_start();
}

require_once 'wt' . DS . 'libs' . DS . 'wt_conf.php';
require_once 'wt' . DS . 'libs' . DS . 'wt_auto.php';

(new Wt_Front())->dispatch();
