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
    ],
    //odp文件路径分割
    'DS' => '_',
    //dao层相关配置
    'DAO' => [
        'BASE_CONFIG'  => [
            'partion'  => '-p', //分表
        ],
        'DOCUMENT_PATH' => '../',
        'FILE_NAME_TEMP'=> [
            'fz' => '',
        ],
        'PARENT_CLASS'  => 'Hk_Common_BaseDao',   //父类
        'DB_NAME'       => 'flipped/zyb_flipped', //DB_NAME
        'DB'            => 'Hk_Service_Db::getDB( $this->_dbName )', //DB
        'LOG_FILE'      => 'Hkzb_Util_FuDao::DBLOG_FUDAO',    //日志文件
        'PARTION_NUM'   => '20',
        'PARTION_TYPE'  => 'self::TYPE_TABLE_PARTION_MOD',
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
        'DOCUMENT_PATH'=> ROOT_PATH . 'Fz' . DS,
        'PARENT_CLASS' => '',   //父类
    ],
    //pageservice层相关配置
    'PAGESERVICE' => [
        'DOCUMENT_PATH'=> ROOT_PATH . 'Fz' . DS,
        'PARENT_CLASS' => '',   //父类
    ],
);
