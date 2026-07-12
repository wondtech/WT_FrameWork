<?php
/***********************************************************************
 *          @Project    : WT FrameWork
 *          @version    : 2.0
 *          @author     : Mogbil Sourketti info[@]wondtech.com
 *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 *          @link       : http://www.wondtech.com
 *          @package    : WT FrameWork (2.0) — grouped routing
 ************************************************************************/

namespace WT\LIBS;

class Wt_Front
{
    const NOT_FOUND_ACTION     = 'NotFoundAction';
    const NOT_FOUND_CONTROLLER = 'WT\Controllers\NotFound_Controller';

    private string $controller = 'home';
    private string $action     = 'index';
    private array  $params     = [];
    private array  $segments   = [];

    public function __construct()
    {
        $this->parseUrl();
    }

    private function parseUrl(): void
    {
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        $path     = substr(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            strlen($basePath)
        );
        $raw = array_values(array_filter(explode('/', trim($path, '/')), fn($s) => $s !== ''));
        $this->segments = array_map(
            fn($s) => preg_replace('/[^a-zA-Z0-9_-]/', '', $s),
            $raw
        );
    }

    private static function classFor(string $group, string $controller): string
    {
        $ns = 'WT\\Controllers\\';
        if ($group !== '') { $ns .= ucfirst($group) . '\\'; }
        return $ns . ucfirst($controller) . '_Controller';
    }

    private static function isController(string $class): bool
    {
        if (!class_exists($class) || !is_subclass_of($class, Wt_Controller::class)) {
            return false;
        }
        // Never route to an abstract base (e.g. Admin\Admin_Base_Controller).
        return !(new \ReflectionClass($class))->isAbstract();
    }

    public function dispatch(): void
    {
        $seg   = $this->segments;
        $class = null;

        if (count($seg) >= 2) {
            $candidate = self::classFor($seg[0], $seg[1]);
            if (self::isController($candidate)) {
                $class            = $candidate;
                $this->controller = $seg[1];
                $this->action     = $seg[2] ?? 'index';
                $this->params     = array_slice($seg, 3);
            }
        }
        if ($class === null) {
            $controller       = $seg[0] ?? 'home';
            $candidate        = self::classFor('', $controller);
            $class            = self::isController($candidate) ? $candidate : self::NOT_FOUND_CONTROLLER;
            $this->controller = $controller;
            $this->action     = $seg[1] ?? 'index';
            $this->params     = array_slice($seg, 2);
        }

        $actionName = $this->action . '_Action';
        $controller = new $class();
        if (!method_exists($controller, $actionName)) {
            $this->action = $actionName = self::NOT_FOUND_ACTION;
        }
        $controller->setController($this->controller);
        $controller->setAction($this->action);
        $controller->setParams($this->params);
        $controller->$actionName();
    }
}