<?php
/**
 * Created by PhpStorm.
 * User: edwinchan
 * Date: 2018/7/12
 * Time: 下午11:41
 */
namespace Creator\App\Odp;

use Creator\App\Creator;
use Creator\Helper\CommonHelper;
use Creator\Helper\PDOWrapper;
use Creator\Helper\TemplateHelper;

class CreatePageService extends Creator
{
    private $_DBHelper;
    private $_TableName;
    private $_OdpConfig;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->_OdpConfig = $GLOBALS['config']['ODP']['DATASERVICE'];
        $this->_DBHelper  = new PDOWrapper();

    }

    public function create()
    {
        $this->setTableName();

        $columnList = $this->getColumnList();

        $allFields  = array();
        foreach ($columnList as $column) {
            $allFields[] = CommonHelper::convertUnderline($column['COLUMN_NAME'],false);
        }
        $allFields = implode(',',$allFields);
//
//        $strFieldsMap = CommonHelper::array2strFormat($fieldsMap);
//        $strTypesMap  = CommonHelper::array2strFormat($typesMap,true);
//
//        $partionKey  = '';
//        $partionType = '';
//        $partionNum  = '';
//        $columnName  = CommonHelper::convertUnderline($columnList[0]['COLUMN_NAME'],false);
//        if ($this->params['base_config'] == true) {
//            $partionKey  = '$this->_partionKey  = ' . "'{$columnName}';";
//            $partionType = '$this->_partionType = ' . $this->_OdpConfig['PARTION_TYPE'].';';
//            $partionNum  = '$this->_partionNum  = ' . $this->_OdpConfig['PARTION_NUM'].';';
//        }
//
//        //拼装数组
        $map = [
            'CLASS_NAME'   => $this->params['base_name'],
            'PARENT_CLASS' => isset($this->_OdpConfig['PARENT_CLASS']) ? 'extends ' . $this->_OdpConfig['PARENT_CLASS'] : '',
            'ALL_FIELDS'   => $allFields,
        ];
//
        $tmpl = TemplateHelper::fetchTemplate('dataservice');
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




