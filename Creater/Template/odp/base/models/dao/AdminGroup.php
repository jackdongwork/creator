<?php
/***************************************************************************
 *
 * Copyright (c) 2016 Zuoyebang.com, Inc. All Rights Reserved
 *
 **************************************************************************/


/**
 * @file    AdminGroup.php
 * @author  cuihang@zuoyebang.com
 * @date    2017/06/01
 * @brief   AdminGroup DAO
 *
 **/
class Dao_AdminGroup extends Hk_Common_BaseDao {
    public function __construct() {
        $this->_dbName = 'flipped/zyb_flipped';
        $this->_db     = Hk_Service_Db::getDB( $this->_dbName );
        $this->_table  = "tblAdminGroup";
        
        $this->arrFieldsMap = array(
            'id'          => 'id',
            'rbacId'      => 'rbacId',
            'title'       => 'title',
            'rPrivileges' => 'rPrivileges',
        );

        $this->arrTypesMap = array(
            'id'          => Hk_Service_Db::TYPE_INT,
            'rbacId'      => Hk_Service_Db::TYPE_INT,
            'title'       => Hk_Service_Db::TYPE_STR,
            'rPrivileges' => Hk_Service_Db::TYPE_JSON,
        );
    }
}