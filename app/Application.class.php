<?php
/**
 * Created by PhpStorm.
 * User: edwinChan
 * Date: 2017/9/9
 * Time: 11:05
 */
namespace APP;
/**
 * 框架初始类
 * Class Frame
 * @package Frame
 */
class Application
{

    static $_argv;
    static $_make;
    static $_action;
    static $_name;


    /**
     * 初始化方法
     */
    public static function run($argv)
    {
        self::$_argv = $argv;
        self::initParam();
        self::initConfig();
        self::initConst();
        self::initAutoLoad();
        self::initDispatch();
    }

    /**
     *
     */
    private static function initParam()
    {
        self::$_make    = self::$_argv[1];
        self::$_action  = self::$_argv[2];
        self::$_name    = self::$_argv[3];
    }

    /**
     *
     */
    private static function initConfig()
    {
        $GLOBALS['config'] = require_once(ROOT_PATH."conf".DS."Conf.php");
    }

//    private static function initRoute()
//    {
//        $p = $GLOBALS['config']['DEFAULT_PLATFORM'];//平台参数//直接从配置文件读取,入口文件已经确定了平台
//        $c = isset($_GET['c'])?$_GET['c']:$GLOBALS['config']['DEFAULT_CONTROLLER'];//控制器参数
//        $a = isset($_GET['a'])?$_GET['a']:$GLOBALS['config']['DEFAULT_ACTION'];//动作参数
//
//        define("CONTROLLER",$c);//控制器名称
//        define("ACTION",$a);  //动作名称
//        define("PLATFORM",$p);//平台名称
//    }

    /**
     * 目录常量设置
     */
    private static function initConst()
    {
        define("TMPL_PATH",ROOT_PATH ."template".DS.strtolower($GLOBALS['FRAME']));//模板路径
    }

    /**
     * 类的自动加载
     */
    private static function initAutoLoad()
    {
        spl_autoload_register(function ($className)
        {
            //将空间中的类名,转成真实的类文件路径
            //空间中的类名 \Home\Controller\StudentController
            //真是的类文件 ./Home/Controller/StudentController
            $filename = ROOT_PATH.str_replace("\\",DS,$className).".class.php";
            //如果类文件存在,则包含
            if (file_exists($filename)) require_once($filename);
        });
    }

    /**
     * 请求分发
     * 创建哪个控制器类的对象?
     * 调用控制器对象的哪个方法
     */
    private static function initDispatch()
    {
//        //构建控制器类名
//        $className = "\\".PLATFORM."\\"."Controller"."\\".CONTROLLER."Controller";
//        //创建控制器类的对象
//        $controllerObj = new $className();
//        //调用控制器对象的方法
//        $action = ACTION;
//        $controllerObj->$action();

        //分发规则
        //make dao Fz_Dao_Unit
        $className = "\\APP\\".$GLOBALS['config']['FRAME']."\\".ucfirst(self::$_make).ucfirst(self::$_action);
        $obj = new $className(self::$_name);
        //根据分割符获取className







    }





}