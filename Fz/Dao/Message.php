<?php
/**
 * file  : Message.php
 * author: chenzhiwen
 * date  : 2018/08/13
 * brief :
 */
class Fz_Dao_Message extends Hk_Common_BaseDao {
	public function __construct() {
		$this->_dbName      = 'flipped/zyb_flipped';
        $this->_db     	    = Hk_Service_Db::getDB( $this->_dbName );
        $this->_logFile     = Hkzb_Util_FuDao::DBLOG_FUDAO;
        $this->_table  	    = 'tblMessage';
        $this->_partionKey  = 'messageId';
        $this->_partionNum  = 20;
        $this->_partionType = self::TYPE_TABLE_PARTION_MOD;
        $this->arrFieldsMap = array(
                  'messageId'       => 'message_id',
                  'messageType'     => 'message_type',
                  'announcementId'  => 'announcement_id',
                  'toPlatform'      => 'to_platform',
                  'toUserUid'       => 'to_user_uid',
                  'content'         => 'content',
                  'status'          => 'status',
                  'isRead'          => 'is_read',
                  'operatorUid'     => 'operator_uid',
                  'operator'        => 'operator',
                  'deleted'         => 'deleted',
                  'createTime'      => 'create_time',
                  'updateTime'      => 'update_time',
                  'extData'         => 'ext_data',
        );
        $this->arrTypesMap = array(
                  'messageId'       => Hk_Service_Db::TYPE_INT,
                  'messageType'     => Hk_Service_Db::TYPE_INT,
                  'announcementId'  => Hk_Service_Db::TYPE_INT,
                  'toPlatform'      => Hk_Service_Db::TYPE_INT,
                  'toUserUid'       => Hk_Service_Db::TYPE_INT,
                  'content'         => Hk_Service_Db::TYPE_STR,
                  'status'          => Hk_Service_Db::TYPE_INT,
                  'isRead'          => Hk_Service_Db::TYPE_INT,
                  'operatorUid'     => Hk_Service_Db::TYPE_INT,
                  'operator'        => Hk_Service_Db::TYPE_STR,
                  'deleted'         => Hk_Service_Db::TYPE_INT,
                  'createTime'      => Hk_Service_Db::TYPE_INT,
                  'updateTime'      => Hk_Service_Db::TYPE_INT,
                  'extData'         => Hk_Service_Db::TYPE_STR,
        );
    }
}

