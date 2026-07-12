<?php
/***********************************************************************
# *          @Project    : WT Insurance
# *          @version    : 2.0
# *          @author     : Mogbil Sourketti info[@]wondtech.com
# *          @copyright  : 2020 WondTech for Integrated Digital Solutions
# *          @link       : http://www.wondtech.com
# *          @package    : WT FrameWork (2.0)
# ************************************************************************/

namespace WT\Models;

use WT\LIBS\Wt_Model;

class Users_Model extends Wt_Model {

    public $u_id, $u_fullName, $u_email, $u_password, $u_mobile, $u_photo, $u_eid, $u_license,  $u_regDate, $u_upDate, $u_ip, $u_state;

    protected static $tableName = 'users';
    protected static $pKey = 'u_id';

    public function __construct(){}

    protected static $tableSchema = array(
        'u_fullName'   => self::DATA_TYPE_STR,
        'u_email'      => self::DATA_TYPE_STR,
        'u_password'   => self::DATA_TYPE_STR,
        'u_mobile'     => self::DATA_TYPE_STR,
        'u_photo'      => self::DATA_TYPE_FIL,
        'u_eid'        => self::DATA_TYPE_FIL,
        'u_license'    => self::DATA_TYPE_FIL,
        'u_regDate'    => self::DATA_TYPE_STR,
        'u_upDate'     => self::DATA_TYPE_STR,
        'u_ip'         => self::DATA_TYPE_STR,
        'u_state'      => self::DATA_TYPE_BOOL
    );
}