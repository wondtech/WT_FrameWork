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

// Application time zone — keeps PHP date() aligned with the MySQL session time
// zone set in Wt_DB. Override with APP_TZ in .env.
date_default_timezone_set($_ENV['APP_TZ'] ?? 'Asia/Riyadh');

// Error display: off in production. Set APP_DEBUG=true in .env for local development.
$wtDebug = filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $wtDebug ? '1' : '0');
ini_set('log_errors', '1');
// E_DEPRECATED is always suppressed: the only source is the vendored Smarty
// library (dynamic-property deprecations on PHP 8.2+), which we must not edit.
error_reporting($wtDebug ? E_ALL & ~E_DEPRECATED : E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// ── Baseline security headers (all responses) ─────────────────────────────
$wtHttps = (($_SERVER['HTTPS'] ?? '') === 'on') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');            // clickjacking guard (esp. admin)
header('Referrer-Policy: strict-origin-when-cross-origin');
if ($wtHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// ── Global exception handler: never leak a stack trace to the client ───────
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
    // Harden the session cookie: HttpOnly + SameSite=Lax (blocks cross-site POST CSRF),
    // Secure automatically when served over HTTPS.
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
