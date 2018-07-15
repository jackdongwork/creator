<?php
/**
 * Created by PhpStorm.
 * User: edwinchan
 * Date: 2018/7/11
 * Time: 下午9:44
 */

//定义部分常量
define("DS",DIRECTORY_SEPARATOR);         //目录分割符
define("ROOT_PATH",getcwd().DS);          //根目录
define("APP_PATH",ROOT_PATH."app".DS);    //平台应用目录
define("CONF_PATH",ROOT_PATH."conf".DS);  //配置文件目录

//包含框架初始类文件
require_once(APP_PATH."Application.class.php");
//框架初始化
\APP\Application::run($argv);