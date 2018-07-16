<?php
/**
 * Created by PhpStorm.
 * User: edwinchan
 * Date: 2018/7/12
 * Time: 下午11:40
 */
namespace Creator\App\Odp;

use Creator\App\Creator;
use Creator\Helper\CommonHelper;
use Creator\Helper\PDOWrapper;
use Creator\Helper\TemplateHelper;

class CreateDao extends Creator
{
    private $_DBHelper;
    private $_TableName;
    private $_OdpConfig;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->_OdpConfig = $GLOBALS['config']['ODP']['DAO'];
        $this->_DBHelper  = new PDOWrapper();

    }

    public function create()
    {
        $this->setTableName();
        $columnList = $this->getColumnList();
        $fieldsMap  = array();
        $typesMap = array();
        foreach ($columnList as $column) {
            $fieldsMap[] = [
                CommonHelper::convertUnderline($column['COLUMN_NAME'],false) => $column['COLUMN_NAME'] ,
            ];

            $typesMap[] = [
                CommonHelper::convertUnderline($column['COLUMN_NAME'],false) => $this->_OdpConfig['TYPES_MAP'][$column['DATA_TYPE']],
            ];
        }

        $strFieldsMap = CommonHelper::array2strFormat($fieldsMap);
        $strTypesMap  = CommonHelper::array2strFormat($typesMap,true);

        $partionKey  = '';
        $partionType = '';
        $partionNum  = '';
        if (false) {
            $partionKey  = '$this->_partionKey  = ' . "'{$columnList[0]['COLUMN_NAME']}';";
            $partionType = '$this->_partionType = ' . $this->_OdpConfig['PARTION_TYPE'].';';
            $partionNum  = '$this->_partionNum  = ' . $this->_OdpConfig['PARTION_NUM'].';';
        }

        //拼装数组
        $map = [
            'CLASS_NAME'   => 'Unit',
            'PARENT_CLASS' => 'extends ' . $this->_OdpConfig['PARENT_CLASS'],
            'DB_NAME'      => $columnList[0]['TABLE_SCHEMA'],
            'DB'           => $this->_OdpConfig['DB'],
            'LOG_FILE'     => $this->_OdpConfig['LOG_FILE'],
            'DB_TABLE'     => $this->_TableName,
            'FIELDS_MAP'   => $strFieldsMap,
            'TYPES_MAP'    => $strTypesMap,
            'PARTION_KEY'  => $partionKey,
            'PARTION_NUM'  => $partionType,
            'PARTION_TYPE' => $partionNum,
        ];

        $tmpl = TemplateHelper::fetchTemplate('dao');
        $this->content = TemplateHelper::parseTemplateTags($map,$tmpl);
        $this->writeToFile();

    }



    public function setTableName()
    {
        $this->_TableName = $this->params['db_name'];
    }

    public function getColumnList()
    {
        //获取数据库
        $sql = "select * from COLUMNS where TABLE_NAME = '{$this->_TableName}'";
        return $columnList = $this->_DBHelper->fetchAll($sql);
    }

}