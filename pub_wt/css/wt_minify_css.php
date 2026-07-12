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

$file = basename($_GET['file'] ?? '');
$path = __DIR__ . '/' . $file;
if (!$file || !file_exists($path)) {
    http_response_code(404);
    exit;
}
header('Content-Type: text/css; charset=UTF-8');
header('Cache-Control: max-age=2592000');
if (str_contains($file, '.min.')) {
    readfile($path);
    exit;
}
$css = file_get_contents($path);
$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
$css = preg_replace('/\s+/', ' ', $css);
$css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
echo trim($css);
