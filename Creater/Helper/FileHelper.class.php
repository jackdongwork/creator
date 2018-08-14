<?php
/**
 * Class File 与文件相关的助手类
 */
namespace Creater\Helper;
class FileHelper
{
    /**
     * 判断目录是否存在，如果不存在则创建，可递归创建所有的父目录
     * @param $path
     */
    public static function mkdir($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * 创建一组路径
     * @param array $paths
     */
    public static function mkdirs($paths = [])
    {
        foreach ($paths as $path) {
            FileHelper::mkdir($path);
        }
    }

    /**
     * 将内容写入到文件的方法
     * @param $content
     * @param $path
     * @param $fileName
     */
    public static function writeToFile($content,$path,$fileName)
    {
        $_file = $path . '/' . $fileName;
        if ($path !== '') {
            FileHelper::mkdir($path);
            $ret = file_put_contents($_file, $content);
            echo empty($ret) ? 'CREATE FAIL !' . PHP_EOL : 'CREATE SUCCESS !' . PHP_EOL;
        }
    }
}