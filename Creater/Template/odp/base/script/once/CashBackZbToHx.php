<?php
/**
 * @file CashBackZbToHx.php
 * @author xujiajun@zuoyebnag.com
 * @date 18-7-11 下午5:27
 * @brief 直播课团报课转浣熊返现一次性脚本
 */
Bd_Init::init('misfz');


class CashBackZbToHx
{
    private $_mod = 0;
    private $_uids = '';
    private $_activityId;
    private $_objDsStudentCourse;
    private $_userInfo;
    private $_amount;
    private $_comment;
    private $_courseIds;

    const mod1 = 1;//过滤多次返现
    const mod2 = 2;//不过滤多次返现
    const mod3 = 3;//直接追加到文件
    const mod4 = 4;//直接将uid置为发送的用户id
    const logDir = '/home/homework/app/misfz/script/log';

    public function __construct($mod, $uids)
    {
        $this->_mod = $mod;
        $this->_uids = !empty($uids) ? array_unique(explode(',', $uids)) : [];
        $this->_objDsStudentCourse = new Hkzb_Ds_Fudao_StudentCourse();
        $this->_userInfo = new Hk_Ds_User_Ucloud();
        $this->_activityId = 2;
        $this->_amount = 3900;
        $this->_comment = '英语班课升级浣熊-奖学金提现';
        $this->_courseIds = [71752, 71751, 70295, 70293, 70392, 70393, 70391, 82129, 82126, 70390, 82128, 82127, 70394];
        if ( $this->_mod == self::mod4) {
            $this->_amount = 1;
        }
    }

    public function execute()
    {
        //读取已发送用户作业帮uid
        $oldUidStr = $this->getOldUids();
        $oldUids = !empty($oldUidStr) ? explode(',', $oldUidStr) : [];
        $start = count($oldUids) + 1;

        //写入文件失败,追加数据，保证发送数据的准确性
        if ( $this->_mod == self::mod3 ) {
            $this->reWrite($oldUidStr);
            echo '写入完成';
            exit;
        }
        $sendYes = $needSend = $failAdd = $sendNoExist = $success = $noBuy = [];
        $retWrite = true;
        if ( empty($this->_uids) ) {
            echo '没有有效的用户id，无需处理';
            exit;
        }

        //判断过滤后的uid是否购买大课
        foreach ($this->_uids as $uid) {
            //获取要发送用户作业帮uid是否在已发送用户中，识别抛出
            if ( $this->_mod == self::mod1 && in_array($uid, $oldUids) ) {
                $sendYes[] = $uid;
                continue;
            }

            $isBuyCourse = $this->_objDsStudentCourse->getStudentCourseInfoArr($uid, $this->_courseIds, ['studentUid']);
            if ( !empty($isBuyCourse) ) {
                $needSend[] = $uid;
            } else {
                $noBuy[] = $uid;
            }
        }

        //谨慎使用模式4
        if ( $this->_mod == self::mod4) {
            $needSend = $this->_uids;
        }

        //发送，并记录到返现用户uid的文件
        if ( !empty($needSend) ) {
            foreach ($needSend as $needUid) {
                $uid = $needUid;
                //用户不存在即抛出
                $userInfo = $this->_userInfo->getUserInfo($uid);
                if ( empty($userInfo) ) {
                    $sendNoExist[] = $uid;
                    continue;
                }

                //获取用户昵称
                $userName = $userInfo['uname'];
                $activityId = $this->_activityId;
                $comment = $this->_comment;
                $amount = $this->_amount;
                //充值时候，传入的keyId为在业务逻辑中
                $rechargeInfo = $this->recharge($uid, $userName, $start, $activityId, $comment, $amount);
                if ( $rechargeInfo === false ) {
                    $failAdd[] = $uid;
                } else {
                    $success[] = $uid;
                    //如果没有用户信息
                    $start++;
                }
            }

            //写入发送用户文件
            if (!empty($success)) {
                $result = !empty($oldUidStr) ? $oldUidStr . ',' . implode(',', $success) : implode(',', $success);
                $retWrite = $this->writeUids($result);
                if ( $retWrite === false ) {
                    $retWrite = $this->writeUids($result);
                }
            }
        }

        $bodyStr = '';
        if ( $retWrite === false ) {
            $bodyStr .= '发送结果写入文件失败，需要写入文件!!!!!!!!!!' . "\n\n";
        }

        if ( !empty($success) ) {
            $bodyStr .= '返现成功的uids，备份数据:'. json_encode($success) . "\n\n";
        } else {
            $bodyStr .= '没有满足发送条件的用户id，请检查:'. json_encode($success) . "\n\n";
        }

        if ( !empty($noBuy) ) {
            $bodyStr .= '未购买大课用户uid，谨慎过滤:'. json_encode($noBuy) . "\n\n";
        }

        if ( !empty($failAdd) ) {
            $bodyStr .= '返现失败的uids，重复操作:'. json_encode($failAdd) . "\n\n";
        }

        if ( !empty($sendYes) ) {
            $bodyStr .= '之前已经返现过的uids，谨慎过滤:'. json_encode($sendYes) . "\n\n";
        }

        if ( !empty($sendNoExist) ) {
            $bodyStr .= '不正确的用户uids，需要检查:'. json_encode($sendNoExist) . "\n\n";
        }

        Hk_Util_Mail::sendMail("xujiajun@zuoyebang.com", "浣熊英语返现" . date('Ymd'), $bodyStr);
    }

    /**
     * 写入文件失败,追加数据，保证发送数据的准确性
     */
    private function reWrite($oldUidStr)
    {
        $str = implode(',', $this->_uids);
        $result = !empty($oldUidStr) ? $oldUidStr . ',' . $str : $str;
        $retWrite = $this->writeUids($result);
        if ( $retWrite === false ) {
            $retWrite = $this->writeUids($result);
        }
        if ( $retWrite === false ) {
            Hk_Util_Mail::sendMail("xujiajun@zuoyebang.com", "发送结果写入文件失败，需要写入文件" . date('Ymd'), $str);
        } else {
            Hk_Util_Mail::sendMail("xujiajun@zuoyebang.com", "返现成功的uids，备份数据" . date('Ymd'), $str);
        }
    }

    /**
     * 充值
     * @param $uid
     * @param $userName
     * @param $keyId
     * @param $activityId
     * @param $comment
     * @param $amount
     * @return bool|mix
     */
    private function recharge($uid, $userName, $keyId, $activityId, $comment, $amount)
    {
        if ( intval($uid) <= 0 ) {
            Bd_Log::warning("Error:[param error], Detail[uid:$uid ]");
            return false;
        }
        $arrHeader = array(
            'pathinfo' => 'pay/transfer/recharge',
        );
        $arrParams = array(
            'uid' => intval($uid),
            'userName' => strval($userName),
            'keyId' => intval($keyId),
            'comment' => strval($comment),
            'activityId' => intval($activityId),
            'amount' => intval($amount),

        );
        $ret = $this->requestCoupon($arrHeader, $arrParams);
        return $ret;
    }

    /**
     * 请求用户提现情况
     * @param  mix $arrHeader
     * @param  mix $arrParams
     * @return mix
     */
    private function requestCoupon($arrHeader, $arrParams)
    {
        $ret = ral('zybpay', 'POST', $arrParams, 123, $arrHeader);
        if ( false === $ret ) {
            $errno = ral_get_errno();
            $errmsg = ral_get_error();
            $protocol_status = ral_get_protocol_code();
            Bd_Log::warning("Error:[service zybpay connect error], Detail:[errno:$errno errmsg:$errmsg protocol_status:$protocol_status]");
            return false;
        }


        $ret = json_decode($ret, true);
        $errno = intval($ret['errNo']);
        $errmsg = strval($ret['errstr']);
        if ( $errno > 0 ) {
            Bd_Log::warning("Error:[service zybpay process error], Detail:[errno:$errno errmsg:$errmsg]");
            return false;
        }
        return $ret['data'];
    }

    /**
     * 获取所有用户id,不去重,保证keyId不重复
     * @param string $sign
     * @return bool|string
     */
    private function getOldUids($sign = "CashBackZbToHx")
    {
        $filename = self::logDir . '/' . $sign . '.txt';
        $str = file_get_contents($filename);
        return trim($str);
    }

    private function writeUids($log, $sign = "CashBackZbToHx")
    {
        if ( !is_dir(self::logDir) ) {
            mkdir(self::logDir, 0777);
        }
        $filename = self::logDir . '/' . $sign . '.txt';
        $writeRes = file_put_contents($filename, $log);
        return $writeRes;
    }
}

$mod = $argv[1];//1校验是否多次返现 2忽略多次返现 3直接写入发送成功uid
$uids = $argv[2];//uid数组  1,2,3,4

$obj = new CashBackZbToHx($mod, $uids);
$obj->execute();