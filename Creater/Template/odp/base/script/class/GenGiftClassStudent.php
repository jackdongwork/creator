<?php
/**
 * Created by PhpStorm.
 * Time: 2018/2/12 15:48
 * Brief: 生成班级 信息
 * 订单支付成功后，为学生分配班级
 * 每分钟执行
 * * /1 * * * * cd /home/homework/app/misfz/script && /home/homework/php/bin/php GenGiftClassStudent.php >/dev/null 2>&1
 */

Bd_Init::init('misfz');

$obj = new GenGiftClassStudent();
$obj->execute();

class GenGiftClassStudent {

    private $_dbName;
    private $_dbCtrl;
    private $_objRedis;

    public function __construct() {

        $this->_dbName = 'flipped/zyb_flipped';
        $this->_dbCtrl = Hk_Service_Db::getDB($this->_dbName);

        $redisConf = Bd_conf::getConf("/hk/redis/common");
        $this->_objRedis = new Hk_Service_Redis($redisConf['service']);
    }

    public function execute()
    {
        $time = time();
        $queue = $this->_objRedis->get('GEN_GIFT_STUDENT_TO_CLASS');
        $queue = json_decode($queue, true);
        foreach ($queue as $k=>$orderId) {

            $idx = intval($orderId/10000000);
            $sql = "select order_id,student_uid,phone,sku_id,course_id,product_id,status,assign_class,ext_data from tblYZOrder" . $idx . " where order_id = {$orderId}";
            $orderInfo = $this->_dbCtrl->query($sql);
            $orderInfo = $orderInfo[0];
            if (empty($orderInfo)) {
                $content = '【logId】' . LOG_ID."[sql]$sql 订单不存在 [orderId]$orderId";
                self::sendMail($content);
                unset($queue[$k]);
                Bd_Log::warning('赠课分班队列' . $content);
                continue;
            }
            // 已支付，未分配班级的订单
            $status = $orderInfo['status'];
            $assignClass = $orderInfo['assign_class'];
            if ($status != Fz_Ds_YZOrder::STATUS_PAID) {
                $content = '【logId】' . LOG_ID."[sql]$sql 订单未支付 [orderId]$orderId";
                self::sendMail($content);
                unset($queue[$k]);
                Bd_Log::warning('赠课分班队列' . $content);
                continue;
            }
            if ($assignClass == 1) {
                $content = '【logId】' . LOG_ID."[sql]$sql 订单已分班 [orderId]$orderId";
                self::sendMail($content);
                unset($queue[$k]);
                Bd_Log::warning('赠课分班队列' . $content);
                continue;
            }
            // 未绑定的暂不分配
            $studentUid = $orderInfo['student_uid'] ? $orderInfo['student_uid'] : 0;
            $phone = $orderInfo['phone'];
            if (empty($phone)) {
                $content = '【logId】' . LOG_ID."[sql]$sql phone null [orderId]$orderId";
                self::sendMail($content);
                continue;
            }
            $orderId   = $orderInfo['order_id'];
            $skuId     = $orderInfo['sku_id'];
            $courseId  = $orderInfo['course_id'];
            $productId = $orderInfo['product_id'];
            $orderExt  = json_decode($orderInfo['ext_data'], true);

            // 商品
            $sql = "select product_title,segment from tblProduct where product_id = {$productId}";
            $productInfo = $this->_dbCtrl->query($sql);
            if (empty($productInfo)) {
                $content = '【logId】' . LOG_ID."[sql]$sql 商品不存在 [orderId]$orderId";
                self::sendMail($content);
                unset($queue[$k]);
                Bd_Log::warning('赠课分班队列' . $content);
                continue;
            }
            $productInfo = $productInfo[0];

            $productInfo = array(
                'title' => $productInfo['product_title'],
                'segment' => $productInfo['segment'],
            );

            // product sku
            $sql = "select item_id,ext_data from tblProductSku where product_id = {$productId} and sku_id = {$skuId}";
            $skuInfo = $this->_dbCtrl->query($sql);
            if (empty($skuInfo)) {
                $content = '【logId】' . LOG_ID."[sql]$sql 商品sku不存在 [orderId]$orderId";
                self::sendMail($content);
                unset($queue[$k]);
                Bd_Log::warning('赠课分班队列' . $content);
                continue;
            }
            $skuExt = json_decode($skuInfo[0]['ext_data'], true);
            $grade  = $skuExt['grade'] ? $skuExt['grade'] : 0;
            $itemId = $skuInfo[0]['item_id'] ? $skuInfo[0]['item_id'] : 0;
            if (empty($itemId)) {
                $content = '【logId】' . LOG_ID."[sql]$sql 商品itemId不存在 [orderId]$orderId";
                self::sendMail($content);
                unset($queue[$k]);
                Bd_Log::warning('赠课分班队列' . $content);
                continue;
            }

            // product sku live
            $sql = "select live_start,live_stop from tblProductSkuLive where sku_id = {$skuId} and item_id = {$itemId} and deleted = 0 order by live_start asc";
            $skuList = $this->_dbCtrl->query($sql);
            if (empty($skuList)) {
                $content = '【logId】' . LOG_ID."[sql]$sql product sku live null [orderId]$orderId";
                self::sendMail($content);
                unset($queue[$k]);
                Bd_Log::warning('赠课分班队列' . $content);
                continue;
            }
            $cnt = count($skuList);
            $liveStart = $skuList[0]['live_start'];
            // 取末次结束时间
            $liveStop = $skuList[$cnt-1]['live_stop'];
            $skuLiveInfo = array(
                'liveStart' => $liveStart,
                'liveStop'  => $liveStop,
            );

            // 老师培训专区
            $campusId = 21;

            // 已有班级
            $sql = "select class_id,student_cnt from tblClassInfo where sku_id = {$skuId} and campus_id = {$campusId} and deleted = 0 order by class_id asc";
            $arrClass = $this->_dbCtrl->query($sql);
            $joinClass = 0;
            $classId = 0;
            // 开启事务
            $this->_dbCtrl->startTransaction();
            foreach ($arrClass as $class) {

                $classId = $class['class_id'];
                $sql = "update tblClassInfo set student_cnt = student_cnt + 1 where class_id = {$classId}";
                $ret = $this->_dbCtrl->query($sql);
                if (false == $ret) {
                    $this->_dbCtrl->rollback();
                    $content = '【logId】' . LOG_ID."[sql]$sql 更新班级人数 fail [orderId]$orderId";
                    self::sendMail($content);
                    Bd_Log::warning('赠课分班队列' . $content);
                    continue;
                }
                $pk = intval($classId/1000000);
                $ext = bin2hex(json_encode(array('orderId'=>$orderId)));
                $sql = "insert into tblClassStudent" . $pk . "(class_id,student_uid,phone,create_time,update_time,ext_data,campus_id) values($classId,$studentUid,$phone,$time,$time,unhex('{$ext}'),$campusId)";
                $ret = $this->_dbCtrl->query($sql);
                if (false == $ret) {
                    $this->_dbCtrl->rollback();
                    $content = '【logId】' . LOG_ID."[sql]$sql 插班生fail [orderId]$orderId";
                    self::sendMail($content);
                    Bd_Log::warning('赠课分班队列' . $content);
                    continue;
                }
                $joinClass = 1;
                break;
            }

            // 已有班级都满班
            if ($joinClass == 0) {
                $sql = "select count(*) as classCnt from tblClassInfo where product_id = {$productId}";
                $classCnt = $this->_dbCtrl->query($sql);
                $classCnt = $classCnt[0]['classCnt'] ? $classCnt[0]['classCnt'] : 0;
                $className = $productInfo['title'] . ($classCnt + 1) . '班';
                $segment   = $productInfo['segment'];
                $liveStart = $skuLiveInfo['liveStart'];
                $liveStop  = $skuLiveInfo['liveStop'];

                // 新增班级
                $sql = "insert into tblClassInfo(
                          class_name,product_id,course_id,sku_id,grade,campus_id,segment,student_cnt,live_start,live_stop,create_time,update_time)
                        values('{$className}',$productId,$courseId,$skuId,$grade,$campusId,$segment,1,$liveStart,$liveStop,$time,$time)";
                $ret = $this->_dbCtrl->query($sql);
                if (false == $ret) {
                    $this->_dbCtrl->rollback();
                    $content = '【logId】' . LOG_ID."[sql]$sql 新增班级fail [orderId]$orderId";
                    self::sendMail($content);
                    Bd_Log::warning('赠课分班队列' . $content);
                    continue;
                }
                $classId = $this->_dbCtrl->getInsertID();
                $livePk = intval($productId/3000);
                // 当前规格的所有直播课排班
                $sql = "select unit_id,live_start,live_stop from tblProductSkuLive where sku_id = {$skuId} and item_id = {$itemId} and deleted = 0 order by live_start asc";
                $skuList = $this->_dbCtrl->query($sql);
                $sql = "insert into tblLive".$livePk."(product_id,sku_id,course_id,class_id,unit_id,live_start,live_stop,create_time,update_time) values";
                foreach ($skuList as $skuInfo) {
                    $sql .= "($productId,$skuId,$courseId,$classId,$skuInfo[unit_id],$skuInfo[live_start],$skuInfo[live_stop],$time,$time),";
                }
                $sql = rtrim($sql, ',');
                $ret = $this->_dbCtrl->query($sql);
                if (false == $ret) {
                    $this->_dbCtrl->rollback();
                    $content = '【logId】' . LOG_ID."[sql]$sql 新增班级排班fail [orderId]$orderId";
                    self::sendMail($content);
                    Bd_Log::warning('赠课分班队列' . $content);
                    continue;
                }
                // 插班生
                $pk  = intval($classId/1000000);
                $ext = bin2hex(json_encode(array('orderId'=>$orderId)));
                $sql = "insert into tblClassStudent" . $pk . "(class_id,student_uid,phone,create_time,update_time,ext_data,campus_id) values($classId,$studentUid,$phone,$time,$time,unhex('{$ext}'),$campusId)";
                $ret = $this->_dbCtrl->query($sql);
                if (false == $ret) {
                    $this->_dbCtrl->rollback();
                    $content = '【logId】' . LOG_ID."[sql]$sql 插班生fail [orderId]$orderId";
                    self::sendMail($content);
                    Bd_Log::warning('赠课分班队列' . $content);
                    continue;
                }
                $joinClass = 1;
            }
            // 已分配班级
            if ($joinClass == 1) {
                $orderExt['classId'] = $classId;
                $ext = bin2hex(json_encode($orderExt));
                $sql = "update tblYZOrder" . $idx . " set assign_class = 1,ext_data = unhex('{$ext}') where order_id = $orderId";
                $ret = $this->_dbCtrl->query($sql);
                if (false == $ret) {
                    $this->_dbCtrl->rollback();
                    $content = '【logId】' . LOG_ID."[sql]$sql 更新分配班级fail [orderId]$orderId";
                    self::sendMail($content);
                    Bd_Log::warning('赠课分班队列' . $content);
                    continue;
                }
            }
            // tblStudentCourse
            if ($joinClass == 1 && $studentUid) {
                $idx = $studentUid%20;
                $sql = "update tblStudentCourse".$idx." set class_id = {$classId} where student_uid = {$studentUid} and sku_id = {$skuId} and product_id = {$productId}";
                $ret = $this->_dbCtrl->query($sql);
                if (false == $ret) {
                    $this->_dbCtrl->rollback();
                    $content = '【logId】' . LOG_ID."[sql]$sql 更新学生课程班级fail [orderId]$orderId";
                    self::sendMail($content);
                    Bd_Log::warning('赠课分班队列' . $content);
                    continue;
                }
            }

            // 提交事务
            $this->_dbCtrl->commit();
            unset($queue[$k]);
        }

        // 重新 置队列
        $newQueue = array_values($queue);
        $this->_objRedis->set('GEN_GIFT_STUDENT_TO_CLASS', json_encode($newQueue));
    }

    private static function sendMail($content) {
        // 邮件报警
        $idc = Bd_Conf::getConf('idc/cur');
        if ($idc == 'yun') {
            $subject = '【翻转 - 赠课分班队列报警】';
            Hk_Util_Mail::sendMail('shaohuan@zuoyebang.com', $subject, $content);
        }
    }
}