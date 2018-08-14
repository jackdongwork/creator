<?php
/**
 * Created by PhpStorm.
 * Time: 2018/6/2
 * Brief: 直播课开课前三天微信消息提醒
 * 每天上午8点执行
 * 0 8 * * *  cd /home/homework/app/misfz/script && /home/homework/php/bin/php BeginRemindBeforeThreeDays >/dev/null 2>&1
 */

Bd_Init::init('misfz');

$obj = new BeginRemindBeforeThreeDays();
$obj->execute();

class BeginRemindBeforeThreeDays
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
        $sTime = strtotime('+3 day 00:00:00');
        $eTime = strtotime('+4 day 00:00:00');
        $sql = "select id,course_id,unit_id,class_id,live_start from tblLive0 where live_start > {$sTime} and live_start < {$eTime} and deleted = 0";

        $liveList = $this->_dbCtrl->query($sql);
        if (empty($liveList)) {
            exit('3天后没有直播课');
        }

        foreach ($liveList as $live) {
            //判断直播课是否为第一节直播课，是则push消息提醒
            $sql = "select course_id,unit_list from tblCourse where course_id = {$live['course_id']} and deleted = 0";
            $courseInfo = $this->_dbCtrl->query($sql);
            if (empty($courseInfo)) {
                self::sendMail("直播课id：{$live['id']} 未配置课程");
                continue;
            }

            $courseInfo = $courseInfo[0];
            $unitList = json_decode($courseInfo['unit_list'], true);
            if (empty($unitList)) {
                self::sendMail("课程id：{$live['course_id']} 未配置单元");
            }

            if ($live['unit_id'] == $unitList[0]) {
                $time = date('Y-m-d H:i', $live['live_start']);
                $res = $this->_objDsWechatPush->pushBeginClassRemind($live['class_id'], $time, Fz_Ds_WechatPush::BEGIN_CLASS_REMIND_BEFORE_THREE_DAYS);
                if (!empty($res)) {
                    self::sendMail("直播课id：{$live['id']} 上课前三天推送微信消息失败 详情：" . var_export($res, true));
                }
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