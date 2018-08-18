<?php
/**
 * Created by PhpStorm.
 * User: edwinchan
 * Date: 2018/7/15
 * Time: 下午3:10
 */
return array(
    //odp的模板类型
    'TEMPLATES' => [
        'dao'           => 'dao.tmpl',
        'dataservice'   => 'dataservice.tmpl',
        'pageservice'   => 'pageservice.tmpl',
        'controller'    => 'controller.tmpl',
        'action'        => 'action.tmpl',
    ],
    'DB' => [
        'PREFIX' => 'tbl',
    ],
    //odp文件路径分割
    'DS' => '_',
    //dao层相关配置
    'DAO' => [
        'BASE_CONFIG'  => [
            //分表
            'PARTION'  => '-p',
        ],
        'DOCUMENT_PATH' => '../desktc/models/', //../
        'FILE_NAME_TEMP'=> [
            'Fz' => 'phplib',
            'fz' => 'phplib',
//            'Ds' => 'ds',
        ],
        'PARENT_CLASS'  => 'Hk_Common_BaseDao',   //父类
        'DB_NAME'       => 'flipped/zyb_flipped', //DB_NAME
        'DB'            => 'Hk_Service_Db::getDB( $this->_dbName )', //DB
        'LOG_FILE'      => 'Hkzb_Util_FuDao::DBLOG_FUDAO',    //日志文件
        'PARTION'       => [
            //取模分表
            'MOD' => [
                'PARTION_NUM'   => '20',
                'PARTION_TYPE'  => 'self::TYPE_TABLE_PARTION_MOD',
            ],
            //固定大小分表
            'MUL' => [
                'PARTION_NUM'   => '3000',
                'PARTION_TYPE'  => 'self::TYPE_TABLE_PARTION_MUL',
            ],
        ],

        'TYPES_MAP'     => [
            'bigint'     => 'Hk_Service_Db::TYPE_INT',
            'blob'       => 'Hk_Service_Db::TYPE_INT',
            'char'       => 'Hk_Service_Db::TYPE_STR',
            'date'       => 'Hk_Service_Db::TYPE_STR',
            'datetime'   => 'Hk_Service_Db::TYPE_STR',
            'int'        => 'Hk_Service_Db::TYPE_INT',
            'longblob'   => 'Hk_Service_Db::TYPE_INT',
            'mediumblob' => 'Hk_Service_Db::TYPE_INT',
            'smallint'   => 'Hk_Service_Db::TYPE_INT',
            'text'       => 'Hk_Service_Db::TYPE_STR',
            'time'       => 'Hk_Service_Db::TYPE_STR',
            'timestamp'  => 'Hk_Service_Db::TYPE_STR',
            'tinyint'    => 'Hk_Service_Db::TYPE_INT',
            'varchar'    => 'Hk_Service_Db::TYPE_STR',
        ],
        'TYPE_JSON'      => 'Hk_Service_Db::TYPE_JSON',
        'TYPE_JSON_FLAG' => 'json',
    ],
    //dataservice层相关配置
    'DATASERVICE' => [
        'DOCUMENT_PATH'=> '../desktc/models/',//ROOT_PATH . 'Fz' . DS
        'PARENT_CLASS' => '',   //父类
    ],
    //pageservice层相关配置
    'PAGESERVICE' => [
        'DOCUMENT_PATH'=> '../desktc/models/',//ROOT_PATH . 'Fz' . DS
        'PARENT_CLASS' => '',   //父类
    ],
    //构建模块
    'APP' => [
        'DOCUMENT_PATH' => '../',
        'BASE_CONFIG'   => [
            'NAMESPACE' => '-c'
        ],
    ],
    //all
    'ALL' => [
        'DOCUMENT_PATH' => [
            'ACTION'      => '../desktc/',
            'CONTROLLER'  => '../desktc/',
            'DAO'         => '../desktc/models/dao/',       //../phplib/dao/
            'DATASERVICE' => '../desktc/models/service/data/',  //../phplib/ds/
            'PAGESERVICE' => '../desktc/models/service/page/',
        ]
    ],
    //controller
    'CONTROLLER' => [
        'DOCUMENT_PATH' => '../desktc/',
        'PARENT_CLASS'  => 'Ap_Controller_Abstract',   //父类
    ],
    //action
    'ACTION' => [
        'DOCUMENT_PATH' => '../desktc/',
        'PARENT_CLASS'  => 'DeskTc_Action_Base',   //父类
        'BASE_CONFIG'  => [
            'MIDDLE_NAME' => '-n',
        ],
    ],

);
