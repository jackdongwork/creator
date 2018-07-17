<?php
/**
 * Created by PhpStorm.
 * User: edwinchan
 * Date: 2018/7/15
 * Time: 下午4:09
 */
namespace Creator\App;
use Creator\Helper\FileHelper;
use Creator\Helper\TemplateHelper;

class Creator
{
    //生成的文本内容
    protected $content = '';
    //用于设置的生成器参数
    protected $params = [
        //生成目标文件的路径
        'path' => '',
        //生成目标文件的相对文件名
        'file_name' => '',
        //生成目标文件的模板
        'template' => '',
        //项目数据
        'project' => [],
        //数据库名称
        'db_name' => '',
    ];


    public function __construct($params)
    {
        $this->setParams($params);
    }



//    abstract public function create();

    /**
     * @param array $params
     */
    public function setParams(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * 将内容写入到文件的方法
     */
    public function writeToFile()
    {
        $_file = $this->params['path'] . '/' . $this->params['file_name'];
        if ($this->content !== '') {
            FileHelper::mkdir($this->params['path']);
            file_put_contents($_file, $this->content);
            echo "success!" . PHP_EOL;
        }
    }


}