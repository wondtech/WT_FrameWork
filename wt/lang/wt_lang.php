<?php
/***********************************************************************
# *          @Project    : WT FrameWork
# *          @version    : 2.0
# *          @author     : Mogbil Sourketti info[@]wondtech.com
# *          @copyright  : 2020 WondTech for Integrated Digital Solutions
# *          @link       : http://www.wondtech.com
# *          @package    : WT FrameWork (2.0)
# ************************************************************************/
namespace WT\LANG;

class Wt_Lang
{
    private static ?array $cache = null;
    public function getLang(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $isAR = ($_SESSION['lang'] ?? 'AR') === 'AR';
        self::$cache = $isAR
            ? (new Wt_AR())->getAr()
            : (new Wt_EN())->getEn();
        return self::$cache;
    }
}