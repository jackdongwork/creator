<?php
/**
 * Created by PhpStorm.
 * Time: 2018/5/2 10:55
 * Brief: 计算续报
 *  每 30 分钟
 */

Bd_Init::init('misfz');

$obj = new GenContinueData();
$obj->execute();

class GenContinueData {

    private $_dbName;
    private $_dbCtrl;
    private $_objDsContinue;

    public function __construct() {

        $this->_dbName = 'flipped/zyb_flipped';
        $this->_dbCtrl = Hk_Service_Db::getDB($this->_dbName);
        $this->_objDsContinue = new Fz_Ds_Continue();
    }

    public function execute() {

        $time = time();

        // 40前后的下单
        $lastTime = $time - 2400;
        $arrSourceId = array(
            Fz_Ds_YZOrder::SOURCE_YZ,
            Fz_Ds_YZOrder::SOURCE_HX,
            Fz_Ds_YZOrder::SOURCE_ZYB,
        );
        $strSourceId = implode(',', $arrSourceId);
        $sql = "select order_id,phone,product_id from tblYZOrder0 where pay_time >= {$lastTime} and status = 1 and source_id in($strSourceId) and sku_id > 0 and product_id > 0";
        $list = $this->_dbCtrl->query($sql);
        if (empty($list)) {
            exit();
        }
        foreach ($list as $info) {
            $orderId = $info['order_id'];
            $productId = $info['product_id'];
            $sql = "select * from tblContinue where order_id = {$orderId}";
            $orderContinue = $this->_dbCtrl->query($sql);
            if (!empty($orderContinue)) { // 已计算续报
                $content = '【logId】' . LOG_ID."[sql]$sql 订单已计算续报 [orderId]$orderId";
                self::sendMail($content);
                Bd_Log::warning('续报计算' . $content);
                continue;
            }
            $phone = $info['phone'];

            $sql = "select nature,ext_data from tblProduct where product_id = {$productId}";
            $productInfo = $this->_dbCtrl->query($sql);
            $nature = $productInfo[0]['nature'];
            $productExt = $productInfo[0]['ext_data'];
            $productExt = json_decode($productExt, true);
            if (!in_array($nature, array(Fz_Ds_Product::NATURE_LONG, Fz_Ds_Product::NATURE_TRAINING))) { // 非长期课不计算
                $content = '【logId】' . LOG_ID."[sql]$sql 订单非长期课不计算续报 [orderId]$orderId";
                self::sendMail($content);
                Bd_Log::warning('续报计算' . $content);
                continue;
            }
            // 商品标记是否算续保
            if ($productExt['countAsContinue'] != Fz_Ds_Product::COUNT_AS_CONTINUE) {
                $content = '【logId】' . LOG_ID."[sql]$sql 标记不算续报 [orderId]$orderId";
                self::sendMail($content);
                Bd_Log::warning('续报计算' . $content);
                continue;
            }

            // 用户其他分班订单
            $sql = "select order_id,product_id,ext_data from tblYZOrder0 where phone = {$phone} and status = 1 and source_id in($strSourceId) and sku_id > 0 and product_id > 0 and assign_class = 1 and order_id != {$orderId}";
            $arrOrder = $this->_dbCtrl->query($sql);
            if (empty($arrOrder)) { // 之前无订单
                $content = '【logId】' . LOG_ID."[sql]$sql 之前无订单不计算续报 [orderId]$orderId";
                self::sendMail($content);
                Bd_Log::warning('续报计算' . $content);
                continue;
            }
            // 统计
            $arrInClassInfo  = array();
            $arrOutClassInfo = array();
            $earlyLiveStart  = 0;
            $lastLiveStop    = 0;
            foreach ($arrOrder as $userOrder) {

                $preOrderId = $userOrder['order_id'];
                if ($preOrderId == $orderId) {
                    continue;
                }
                $ext = $userOrder['ext_data'];
                $ext = json_decode($ext, true);
                // 调班或者调课
                $preProductId = !empty($ext['productId']) ? intval($ext['productId']) : intval($userOrder['product_id']);

                // 商品信息
                $sql = "select nature from tblProduct where product_id = {$preProductId}";
                $preProductInfo = $this->_dbCtrl->query($sql);
                $preNature = $preProductInfo[0]['nature'];
                if (!in_array($preNature, array(Fz_Ds_Product::NATURE_LONG, Fz_Ds_Product::NATURE_TRAINING, Fz_Ds_Product::NATURE_EXPERIENCE))) {
                    $content = '【logId】' . LOG_ID."[sql]$sql 之前订单非长期课或者体验课不计算续报 [preOrderId]$preOrderId [orderId]$orderId";
                    self::sendMail($content);
                    Bd_Log::warning('续报计算' . $content);
                    continue;
                }
                // 之前的算转化 or 续报
                $continueType = 0;
                if ($preNature == Fz_Ds_Product::NATURE_EXPERIENCE) { // 转化
                    $continueType = Fz_Ds_Continue::TYPE_TRANSFER;
                } else if ($preNature == Fz_Ds_Product::NATURE_LONG || $preNature == Fz_Ds_Product::NATURE_TRAINING) { // 续报
                    $continueType = Fz_Ds_Continue::TYPE_CONTINUE;
                }

                // 班级信息
                $preClassId = intval($ext['classId']);
                if ($preClassId > 0) {
                    $sql = "select teacher_uid,live_start,live_stop from tblClassInfo where class_id = {$preClassId}";
                    $preClassInfo = $this->_dbCtrl->query($sql);
                    $preClassInfo = $preClassInfo[0];
                } else {
                    $preClassInfo = array();
                }
                $teacherUid  = $preClassInfo['teacher_uid'] ? intval($preClassInfo['teacher_uid']) : 0;
                $liveStart   = $preClassInfo['live_start'] ? intval($preClassInfo['live_start']) : 0;
                $liveStop    = $preClassInfo['live_stop'] ? intval($preClassInfo['live_stop']) : 0;
                $liveStop    = 7*86400 + $liveStop;
                // 直播结束前7天内 算老师续报 (最早开课的)
                if ($liveStop >= $time && $time > 0 && $time > $liveStart) {
                    if ($earlyLiveStart == 0) {
                        $earlyLiveStart = $liveStart;
                        $arrInClassInfo = array(
                            'classId'      => $preClassId,
                            'liveStart'    => $liveStart,
                            'teacherUid'   => $teacherUid,
                            'productId'    => $preProductId,
                            'continueType' => $continueType,
                        );
                    } else {
                        if ($earlyLiveStart > $liveStart) {
                            $earlyLiveStart = $liveStart;
                            $arrInClassInfo = array(
                                'classId'      => $preClassId,
                                'liveStart'    => $liveStart,
                                'teacherUid'   => $teacherUid,
                                'productId'    => $preProductId,
                                'continueType' => $continueType,
                            );
                        }
                    }
                // 7天外 算平台学续报 (最晚结课的)
                } else if ($liveStop < $time && $liveStop > 0) {
                    if ($lastLiveStop == 0) {
                        $lastLiveStop = $liveStop;
                        $arrOutClassInfo = array(
                            'productId'    => $preProductId,
                            'continueType' => $continueType,
                        );
                    } else {
                        if ($lastLiveStop < $liveStop) {
                            $lastLiveStop = $liveStop;
                            $arrOutClassInfo = array(
                                'productId'    => $preProductId,
                                'continueType' => $continueType,
                            );
                        }
                    }
                }
            }
            $arrParams = array();
            // 有周期内的情况
            if (!empty($arrInClassInfo)) {
                $arrParams = array(
                    'phone'      => $phone,
                    'productId'  => $arrInClassInfo['productId'],
                    'classId'    => $arrInClassInfo['classId'],
                    'teacherUid' => $arrInClassInfo['teacherUid'],
                    'continueProduct' => $productId,
                    'orderId'         => $orderId,
                    'inCycle'         => Fz_Ds_Continue::IN_CYCLE,
                    'continueType'    => $arrInClassInfo['continueType'],
                );
            } else if (!empty($arrOutClassInfo)) {
                $arrParams = array(
                    'phone'      => $phone,
                    'productId'  => $arrOutClassInfo['productId'],
                    'continueProduct' => $productId,
                    'orderId'         => $orderId,
                    'inCycle'         => Fz_Ds_Continue::OUT_CYCLE,
                    'continueType'    => $arrOutClassInfo['continueType'],
                );
            }
            if (!empty($arrParams)) {
                $continueId = $this->_objDsContinue->addContinue($arrParams);
                if (false === $continueId) {
                    $content = '【logId】' . LOG_ID."[参数]".var_export($arrParams, true)." [orderId]$orderId";
                    self::sendMail($content);
                    Bd_Log::warning('计算续报数据 insert tblContinue fail ' . $content);
                    continue;
                }
            }
        }
    }

    private static function sendMail($content) {
        // 邮件报警
        $idc = Bd_Conf::getConf('idc/cur');
        if ($idc == 'yun') {
            $subject = '【翻转 - 续报统计报警】';
            Hk_Util_Mail::sendMail('shaohuan@zuoyebang.com', $subject, $content);
        }
    }
}