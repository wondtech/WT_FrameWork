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
header('Content-Type: application/javascript; charset=UTF-8');
header('Cache-Control: max-age=2592000');
if (str_contains($file, '.min.')) {
    readfile($path);
    exit;
}
$js = file_get_contents($path);
$js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
$js = preg_replace('/\/\/[^\n]*/', '', $js);
$js = preg_replace('/\s+/', ' ', $js);
echo trim($js);
