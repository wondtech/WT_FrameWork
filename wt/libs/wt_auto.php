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

namespace WT\LIBS;
class AutoLoad
{
    public static function autoload(string $className): void
    {
        if (!str_starts_with($className, 'WT\\')) {return;}
        $className = ltrim(str_replace('WT\\', '', $className), '\\');
        $className = str_replace('\\', DS, $className);
        $className = strtolower($className) . '.php';
        $realBase = realpath(APP_PATH);
        $fullPath = APP_PATH . DS . $className;
        $realFile = realpath($fullPath);
        if ($realFile && str_starts_with($realFile, $realBase)) {
            require_once $realFile;
        }
    }
}
spl_autoload_register(__NAMESPACE__ . '\AutoLoad::autoload');