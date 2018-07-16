<?php
class Unit extends Hk_Common_BaseDao {
	public function __construct() {
		$this->_dbName      = 'zyb_flipped';//手动补全
        $this->_db     	    = Hk_Service_Db::getDB( $this->_dbName );
        $this->_logFile     = Hkzb_Util_FuDao::DBLOG_FUDAO;
        $this->_table  	    = 'friends';
        //手动更改
        //手动更改
        //手动更改
        $this->arrFieldsMap = array(
            'id'    => 'id',
            'name'  => 'name',
            'pass'  => 'pass',
        );
        $this->arrTypesMap = array(
            'id'    => Hk_Service_Db::TYPE_INT,
            'name'  => Hk_Service_Db::TYPE_STR,
            'pass'  => Hk_Service_Db::TYPE_STR,
        );
    }
}

