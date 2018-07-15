<?php
/**
 * Created by PhpStorm.
 * User: edwinchan
 * Date: 2018/7/15
 * Time: 下午4:09
 */
namespace APP;
class CreateBase
{
    private $tpl;
    public function __construct()
    {
        $this->tpl = new TemplateHelper();//模板
    }

    protected function fillTemplate()
    {
        
    }
}