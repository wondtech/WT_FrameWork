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

class Tokens_Model extends Wt_Model {

    public $u_id, $t_ip, $t_date, $t_token;

    protected static $tableName = 'tokens';
    protected static $pKey = 'u_id';

    public function __construct(){}

    protected static $tableSchema = array(
        'u_id'      => self::DATA_TYPE_INT,
        't_ip'      => self::DATA_TYPE_STR,
        't_date'    => self::DATA_TYPE_STR,
        't_token'   => self::DATA_TYPE_STR
    );
}