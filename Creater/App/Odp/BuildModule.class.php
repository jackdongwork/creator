<?php
/**
 * Created by PhpStorm.
 * User: edwinchan
 * Date: 2018/8/14
 * Time: 下午11:13
 */
namespace Creater\App\Odp;

use Creater\Helper\FileHelper;

class BuildModule
{
    private $_Config;
    private $params;

    public function __construct($params)
    {
        $this->params  = $params;
        $this->_Config = $GLOBALS['config']['ODP']['MODULE'];
    }

    /**
     * 创建
     */
    public function build()
    {
        FileHelper::copyFiles(TMPL_PATH . DS . 'base', $this->_Config['DOCUMENT_PATH'] . DS . $this->params['base_name']);
    }

}