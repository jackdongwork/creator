<?php
/**
 * file  : CommonHelper.class.php
 * author: chenzhiwen@zuoyebang.com
 * date  : 2018/7/16
 * brief :
 */
namespace Creator\Helper;
class CommonHelper
{
    public static function convertUnderline ( $str , $ucfirst = true)
    {
        while(($pos = strpos($str , '_'))!==false) {
            $str = substr($str , 0 , $pos).ucfirst(substr($str , $pos+1));
        }
        return $ucfirst ? ucfirst($str) : $str;
    }

    public static function array2strFormat($arr,$akey = false)
    {
        $str = '';
        $maxNum = 0;
        foreach ($arr as $arr2) {
            foreach ($arr2 as $key => $item) {
                $num = strlen($key) + 4;
                $maxNum = $maxNum < $num ? $num : $maxNum;
            }
        }
        foreach ($arr as $arr2) {
            foreach ($arr2 as $key => $item) {
                $num = strlen($key);
                $s = str_pad('=>',$maxNum - $num,' ',STR_PAD_LEFT);
                if ($akey){
                    $s = $s . " ";
                }
                $_s   = '\''.$key .'\''. $s . '\''.$item .'\','.PHP_EOL;
//                print_r($)
                $str .= str_pad($_s,$maxNum + 12,"-",STR_PAD_LEFT);
            }
        }
        return $str;
    }
}