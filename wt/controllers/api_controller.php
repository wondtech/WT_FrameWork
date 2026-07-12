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

use WT\LIBS\Wt_Sec;
use WT\LIBS\Wt_Helper;
use WT\Models\Users_Model;
use WT\Models\Tokens_Model;

class Api_Controller extends Wt_Controller {

    use Wt_Sec;
    use Wt_Helper;
    private string $apiURL = 'https://';
    public function __construct() {
        header("Access-Control-Allow-Origin: ".$this->apiURL);
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        http_response_code(404);
    }

    private static function GoodRequest($msg, $opt=null) {
        http_response_code(200);
        $_msg = $opt == null
            ? '{ "state" : "true", "msg" : "'.$msg.'" }'
            : '{ "state" : "true", "msg" : "'.$msg.'", "opt" : "'.$opt.'" }';
        echo $_msg; exit;
    }
    private static function GoodRequestGetData($msg) {
        http_response_code(200);
        echo json_encode($msg); exit;
    }
    private static function BadRequest($msg) {
        http_response_code(400);
        echo '{ "state" : "false", "msg" : "'.$msg.'" }';
        exit;
    }
    private static function AccessDenied($msg) {
        http_response_code(401);
        echo '{ "state" : "false", "msg" : "'.$msg.'" }';
        exit;
    }
    private static function Token() {
        if(isset($_POST['u_id']) && isset($_POST['token'])) return
            Tokens_Model::wt_getData('WHERE u_id = :u_id AND t_token = :t_token',
                array('u_id' => array(Users_Model::DATA_TYPE_STR, $_POST['u_id']),
                    't_token' => array(Users_Model::DATA_TYPE_STR, $_POST['token'])
                )) ? true : false;
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////// Token API
    public function Token_Action() { http_response_code(200); echo self::Token()?$_POST['token']:'false'; exit; }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////// Default API
    public function Index_Action() {

    }
}
