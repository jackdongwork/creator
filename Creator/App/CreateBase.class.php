<?php
/**
 * Created by PhpStorm.
 * User: edwinchan
 * Date: 2018/7/12
 * Time: 下午11:40
 */
namespace Creator\App;


abstract class CreateBase
{
    /**
     * 配置
     * @var
     */
    protected $_Config;

    /**
     * 模板内容
     * @var string
     */
    protected $content = '';

    /**
     * 参数
     * @var array
     */
    protected $params = [
        //基础文件名
        'base_name' => '',
        //基础配置
        'base_config' => '',
        //生成目标文件的路径
        'path' => '',
        //生成目标文件的相对文件名
        'file_name' => '',
        //数据库名称
        'db_name' => '',
    ];


    public function __construct($params)
    {
        $this->setParams($params);
    }


    /**
     * 设置参数
     * @param array $params
     */
    public function setParams(array $params = [])
    {
        $this->params = $params;
    }


    /**
     * 创建
     */
    abstract public function create();

}