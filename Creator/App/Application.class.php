<?php
/**
 * Created by PhpStorm.
 * User: edwinChan
 * Date: 2017/9/9
 * Time: 11:05
 */
namespace Creator\App;
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
    static $_config;

    static $params = [
        //基础参数
        'base_name' => '',
        //基础参数
        'base_config' => '',
        //生成目标文件的路径
        'path' => '',
        //生成目标文件的相对文件名
        'file_name' => '',
        //生成目标文件的模板
//        'template' => '',
        //项目数据
//        'project' => [],
        //数据库名称
        'db_name' => '',
    ];

    /**
     * 初始化方法
     */
    public static function run($argv)
    {
        self::$_argv = $argv;
        self::initConfig();
        self::initConst();
        self::initParam();
        self::initAutoLoad();

        self::initDispatch();
    }



    /**
     *
     */
    private static function initConfig()
    {
        $GLOBALS['config'] = require_once(CONF_PATH . "Conf.php");
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
        define("TMPL_PATH",ROOT_PATH ."Creator".DS."Template".DS.strtolower($GLOBALS['config']['FRAME'].DS));//模板路径
    }

    /**
     *
     */
    private static function initParam()
    {
        self::$_make    = self::$_argv[1];
        self::$_action  = self::$_argv[2];
        self::$_name    = self::$_argv[3];
        self::$_config  = self::$_argv[4];

        $configName     = $GLOBALS['config']['ODP']['DAO']['DOCUMENT_PATH'] . self::$_name;

        //初始化参数
        self::$params['base_name'] = self::$_name;
        self::$params['base_config'] = self::$_config;
        $DS = $GLOBALS['config']['ODP']['DS'];
        $name = trim(strrchr(self::$_name, $DS),$DS);
        self::$params['path'] = str_replace($DS, DS,substr($configName,0,strrpos($configName, $DS)));
        self::$params['file_name'] =  $name . '.php';
        if (self::$_action == 'dao') {
            self::$params['db_name'] = 'tbl' . $name;
        }
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
        //分发规则
        //make dao Fz_Dao_Unit
        $className = "\\".__NAMESPACE__."\\".ucfirst(strtolower($GLOBALS['config']['FRAME']))."\\".ucfirst(self::$_make).ucfirst(self::$_action);
        $obj       = new $className(self::$params);
        $action    = self::$_make;
        $obj->$action();
        //根据分割符获取className
    }

}