<?php
/**
 * file  : Message.php
 * author: chenzhiwen
 * date  : 2018/08/15
 * brief :
 */
class Message extends Hk_Common_BaseDao {
	public function __construct() {
		$this->_dbName      = 'flipped/zyb_flipped';
        $this->_db     	    = Hk_Service_Db::getDB( $this->_dbName );
        $this->_logFile     = Hkzb_Util_FuDao::DBLOG_FUDAO;
        $this->_table  	    = '';
        
        
        
        $this->arrFieldsMap = array(

        );
        $this->arrTypesMap = array(

        );
    }
}

