<?php
/***********************************************************************
# *          @Project    : WT FrameWork
# *          @version    : 2.0
# *          @author     : Mogbil Sourketti info[@]wondtech.com
# *          @copyright  : 2020 WondTech for Integrated Digital Solutions
# *          @link       : http://www.wondtech.com
# *          @package    : WT FrameWork (2.0)
# ************************************************************************/

namespace WT\Controllers;
use WT\LIBS\Wt_Controller;

class Home_Controller extends Wt_Controller{

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////// Default Page
    public function Index_Action() {
        $tpl = $this->view();
        $tpl->view('index.tpl');
    }
}
