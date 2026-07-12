<?php
/***********************************************************************
 *          @Project    : WT FrameWork
 *          @version    : 2.0
 *          @author     : Mogbil Sourketti info[@]wondtech.com
 *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 *          @link       : http://www.wondtech.com
 *          @package    : WT FrameWork (2.0) — Improved
 ************************************************************************/

function wt_loadEnv(?string $path = null): void
{
    if ($path === null) {
        $path = dirname(__DIR__);
    }
    $file = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . '.env';
    if (!file_exists($file)) {
        error_log('[wt_env] .env file not found in: ' . $path);
        return;
    }
    $realFile   = realpath($file);
    $publicPath = realpath(__DIR__);
    if ($realFile && str_starts_with($realFile, $publicPath)) {
        error_log('[wt_env] .env file must be outside public directory.');
        return;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        error_log('[wt_env] Failed to read .env file.');
        return;
    }
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (empty($key)) {
            continue;
        }
        $value = trim($value, '"\'');
        if (str_contains($value, ' #')) {
            $value = trim(explode(' #', $value)[0]);
        }
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}