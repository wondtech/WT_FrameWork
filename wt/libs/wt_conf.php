<?php
/***********************************************************************
# *          @Project    : WT FrameWork
# *          @version    : 2.0
# *          @author     : Mogbil Sourketti info[@]wondtech.com
# *          @copyright  : 2020 WondTech for Integrated Digital Solutions
# *          @link       : http://www.wondtech.com
# *          @package    : WT FrameWork (2.0)
# ************************************************************************/

if(!defined('DS'))define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', realpath(__DIR__ . DS . '..'));

define('TEMPLATE', APP_PATH.DS.'template'.DS);
define('TEMP_SYS', TEMPLATE.'temp_sys'.DS);

define('TEMPLATE_PATH', TEMPLATE.'home'.DS);
define('ADMIN_TEMPLATE_PATH', TEMPLATE.'admin'.DS);
define('TEMP_C', TEMP_SYS.'templates_c'.DS);
define('TEMP_CONF', TEMP_SYS.'configs'.DS);
define('TEMP_CACHE', TEMP_SYS.'cache'.DS);