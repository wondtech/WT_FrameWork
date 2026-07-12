<?php
/***********************************************************************
 *          @Project    : WT FrameWork
 *          @version    : 2.0
 *          @author     : Mogbil Sourketti info[@]wondtech.com
 *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 *          @link       : http://www.wondtech.com
 *          @package    : WT FrameWork (2.0) — Improved
 ************************************************************************/

namespace WT\LIBS;

use WT\LANG\Wt_Lang;

abstract class Wt_Controller
{
    protected ?Wt_Smarty $tpl        = null;
    protected array      $lang       = [];
    protected array      $params     = [];
    protected string     $action     = '';
    protected string     $controller = '';
    protected array      $actPages   = [
        'act_home', 'act_offers', 'act_orders',
        'act_customers', 'act_users', 'act_settings'
    ];

    public function setController(string $controllerName): void
    {
        $this->controller = $controllerName;
    }

    public function setAction(string $actionName): void
    {
        $this->action = $actionName;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function NotFoundAction(): void
    {
        $this->view();
    }

    private function assignHreflang(): void
    {
        $appUrl  = rtrim($_ENV['APP_URL'] ?? 'https://wondtech.com', '/');
        $reqUri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path    = parse_url($reqUri, PHP_URL_PATH) ?: '/';

        parse_str((string) parse_url($reqUri, PHP_URL_QUERY), $query);
        unset($query['lang']);
        $baseQuery = http_build_query($query);
        $basePath  = $path . ($baseQuery !== '' ? '?' . $baseQuery : '');
        $sep       = $baseQuery !== '' ? '&' : '?';

        $isAr    = ($_SESSION['lang'] ?? 'EN') === 'AR';
        $hrefEn  = $appUrl . $basePath;
        $hrefAr  = $appUrl . $basePath . $sep . 'lang=AR';

        $this->tpl->assign('html_lang',    $isAr ? 'ar' : 'en');
        $this->tpl->assign('canonical_self', $isAr ? $hrefAr : $hrefEn);
        $this->tpl->assign('hreflang_ar',  $hrefAr);
        $this->tpl->assign('hreflang_en',  $hrefEn);
        $this->tpl->assign('hreflang_x',   $hrefEn);
    }

    protected function view(?string $type = null): Wt_Smarty
    {
        $this->tpl  = new Wt_Smarty($type);
        $this->lang = (new Wt_Lang())->getLang();
        foreach ($this->lang as $key => $val) {
            $this->tpl->assign($key, $val);
        }
        $this->assignHreflang();
        foreach ($this->actPages as $actPage) {
            $this->tpl->assign($actPage, '');
        }
        $this->tpl->assign('params', $this->params);
        $this->tpl->assign('wt_version', $_ENV['APP_VERSION'] ?? '2.0');
        if ($this->action === Wt_Front::NOT_FOUND_ACTION) {
            $this->tpl->view('notfound.tpl');
        }

        return $this->tpl;
    }
}