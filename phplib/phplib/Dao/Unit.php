<?php
/**
 * file  : Unit.php
 * author: chenzhiwen
 * date  : 2018/08/13
 * brief :
 */
class Fz_Dao_Unit extends Hk_Common_BaseDao {
	public function __construct() {
		$this->_dbName      = 'flipped/zyb_flipped';
        $this->_db     	    = Hk_Service_Db::getDB( $this->_dbName );
        $this->_logFile     = Hkzb_Util_FuDao::DBLOG_FUDAO;
        $this->_table  	    = 'tblUnit';
        
        
        
        $this->arrFieldsMap = array(
                'unitId'        => 'unit_id',
                'unitName'      => 'unit_name',
                'courseId'      => 'course_id',
                'courseIdList'  => 'course_id_list',
                'content'       => 'content',
                'unitLevel'     => 'unit_level',
                'examList'      => 'exam_list',
                'createTime'    => 'create_time',
                'updateTime'    => 'update_time',
                'taskList'      => 'task_list',
                'status'        => 'status',
                'operatorUid'   => 'operator_uid',
                'operator'      => 'operator',
                'deleted'       => 'deleted',
        );
        $this->arrTypesMap = array(
                'unitId'        => Hk_Service_Db::TYPE_INT,
                'unitName'      => Hk_Service_Db::TYPE_STR,
                'courseId'      => Hk_Service_Db::TYPE_INT,
                'courseIdList'  => Hk_Service_Db::TYPE_JSON,
                'content'       => Hk_Service_Db::TYPE_STR,
                'unitLevel'     => Hk_Service_Db::TYPE_INT,
                'examList'      => Hk_Service_Db::TYPE_STR,
                'createTime'    => Hk_Service_Db::TYPE_INT,
                'updateTime'    => Hk_Service_Db::TYPE_INT,
                'taskList'      => Hk_Service_Db::TYPE_STR,
                'status'        => Hk_Service_Db::TYPE_INT,
                'operatorUid'   => Hk_Service_Db::TYPE_INT,
                'operator'      => Hk_Service_Db::TYPE_STR,
                'deleted'       => Hk_Service_Db::TYPE_INT,
        );
    }
}

