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
        'dao'  => '/template/odp/dao.tmpl',
    ],
    //odp文件路径分割符
    'ODP_DS' => '_',


    //dao层相关配置
    'DAO' => [
        'PARENT_CLASS' => 'Hk_Common_BaseDao',   //父类
        'DB'           => 'Hk_Service_Db::getDB( $this->_dbName )', //DB
        'LOG_FILE'     => 'Hkzb_Util_FuDao::DBLOG_FUDAO',    //日志文件

    ]
);
