<?php
/**
 * Created by PhpStorm.
 * User: edwinchan
 * Date: 2018/7/12
 * Time: 下午11:40
 */
namespace Creater\App\Odp;

use Creater\App\CreateBase;
use Creater\Helper\CommonHelper;
use Creater\Helper\FileHelper;
use Creater\Helper\TemplateHelper;

class CreateAll
{
    private $_DaoParams;
    private $_DsParams;
    private $_PsParams;
    private $_Config;

    public function __construct($params)
    {
        $this->_DaoParams = $params;
        $this->_DsParams  = $params;
        $this->_PsParams  = $params;
        $this->_Config    = $GLOBALS['config']['ODP']['ALL'];
    }

    /**
     * 创建
     */
    public function create()
    {
        $this->_DaoParams['path'] = $this->_Config['DOCUMENT_PATH']['DAO'];
        $this->_DsParams['path']  = $this->_Config['DOCUMENT_PATH']['DATASERVICE'];
        $this->_PsParams['path']  = $this->_Config['DOCUMENT_PATH']['PAGESERVICE'];

        (new CreateDao($this->_DaoParams))->create();
        (new CreateDataService($this->_DsParams))->create();
        (new CreatePageService($this->_PsParams))->create();
    }

}