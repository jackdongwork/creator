<?php
/**
 * Created by PhpStorm.
 * Time: 2018/6/2
 * Brief: 直播课开课前30分钟内微信消息提醒
 * 每30分钟执行
 * * / 30 * * * *  cd /home/homework/app/misfz/script && /home/homework/php/bin/php BeginRemindBeforeThreeDays >/dev/null 2>&1
 */

Bd_Init::init('misfz');

$obj = new BeginRemindBeforeFifteenMins();
$obj->execute();

class BeginRemindBeforeFifteenMins
{
    private $_dbName;
    private $_dbCtrl;
    private $_objDsWechatPush;

    public function __construct() {

        $this->_dbName = 'flipped/zyb_flipped';
        $this->_dbCtrl = Hk_Service_Db::getDB($this->_dbName);
        $this->_objDsWechatPush = new Fz_Ds_WechatPush();
    }

    public function execute()
    {
        $sTime = time();
        $eTime = $sTime + 30*60;

        $sql = "select id,course_id,unit_id,class_id,live_start from  tblLive0 where live_start > {$sTime} and live_start < {$eTime} and deleted = 0";

        $liveList = $this->_dbCtrl->query($sql);
        if (empty($liveList)) {
            exit('30分钟内没有直播课');
        }

        foreach ($liveList as $live) {
            $time = date('Y-m-d H:i', $live['live_start']);
            $res = $this->_objDsWechatPush->pushBeginClassRemind($live['class_id'], $time, Fz_Ds_WechatPush::BEGIN_CLASS_REMIND_BEFORE_FIFTEEN_MINS);
            if (!empty($res)) {
                self::sendMail("直播课id：{$live['id']} 上课前30分钟推送微信消息失败 详情：" . var_export($res, true));
            }
        }
    }

    private static function sendMail($content) {
        // 邮件报警
        $idc = Bd_Conf::getConf('idc/cur');
        if ($idc == 'yun') {
            $subject = '【微信-消息提醒push失败】';
            Hk_Util_Mail::sendMail('lipengyin@zuoyebang.com', $subject, $content);
        }
    }
}