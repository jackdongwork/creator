<?php
class Unit extends Hk_Common_BaseDao {
	public function __construct() {
		$this->_dbName 		= 'homework_fudao';
        $this->_db     		= 'Hk_Service_Db::getDB( $this->_dbName )';
        $this->_logFile     = 'Hkzb_Util_FuDao::DBLOG_FUDAO';
        $this->_table  		= 'tblAccount';
        $this->_partionKey  = "student_uid";
        $this->_partionType = self::TYPE_TABLE_PARTION_MOD;
        $this->_partionNum  = 20;
        $this->arrFieldsMap = array(
            ---'id'              =>'id',
'nickname'        =>'nickname',
-'type'            =>'type',
'phone'           =>'phone',
--'uid'             =>'uid',
'account'         =>'account',
'password'        =>'password',
'wechatAccount'   =>'wechat_account',
'wechatPassword'  =>'wechat_password',
'createTime'      =>'create_time',
'updateTime'      =>'update_time',
'operator'        =>'operator',
'extData'         =>'ext_data',
-'used'            =>'used',
'groupType'       =>'group_type',

        );
        $this->arrTypesMap = array(
            'id'              => 'Hk_Service_Db::TYPE_INT',
'nickname'        => 'Hk_Service_Db::TYPE_STR',
'type'            => 'Hk_Service_Db::TYPE_INT',
'phone'           => 'Hk_Service_Db::TYPE_STR',
'uid'             => 'Hk_Service_Db::TYPE_INT',
'account'         => 'Hk_Service_Db::TYPE_INT',
'password'        => 'Hk_Service_Db::TYPE_STR',
'wechatAccount'   => 'Hk_Service_Db::TYPE_STR',
'wechatPassword'  => 'Hk_Service_Db::TYPE_STR',
'createTime'      => 'Hk_Service_Db::TYPE_INT',
'updateTime'      => 'Hk_Service_Db::TYPE_INT',
'operator'        => 'Hk_Service_Db::TYPE_STR',
'extData'         => 'Hk_Service_Db::TYPE_STR',
'used'            => 'Hk_Service_Db::TYPE_INT',
'groupType'       => 'Hk_Service_Db::TYPE_INT',

        );
    }
}

