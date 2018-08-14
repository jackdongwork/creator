<?php
/**
 * Created by PhpStorm.
 * Time: 2018/2/13 11:11
 * Brief: 更新班级状态，未开课，开课中，已结课
 * * /10 * * * * cd /home/homework/app/misfz/script && /home/homework/php/bin/php SynClassStatus.php >/dev/null 2>&1
 */

Bd_Init::init('misfz');

$obj = new SynClassStatus();
$obj->execute();

class SynClassStatus {

    private $_dbName;
    private $_dbCtrl;

    public function __construct() {

        $this->_dbName = 'flipped/zyb_flipped';
        $this->_dbCtrl = Hk_Service_Db::getDB($this->_dbName);
    }

    public function execute() {

        $time = time();

        $timeBeforeContinue = $time - 7 * 86400; //扣除掉续报周期的时间

        // 未开课 -> 开课中
        $sql = "update tblClassInfo set status = 1 where live_start < {$time} and {$timeBeforeContinue} < live_stop and deleted = 0 and status = 0";
        $this->_dbCtrl->query($sql);

        // 开课中 -> 已结课
        $sql = "update tblClassInfo set status = 2 where {$timeBeforeContinue} > live_stop and deleted = 0 and status = 1";
        $this->_dbCtrl->query($sql);

    }
}