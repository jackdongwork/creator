<?php
/**
 * Created by PhpStorm.
 * Time: 2018/6/2 11:09
 * Brief: 薪酬计算
 * 每月第1天执行 上个月数据
 * 0 2 1 * * cd /home/homework/app/misfz/script && /home/homework/php/bin/php GenSalary.php >/dev/null 2>&1
 */
Bd_Init::init('misfz');

$month = $argv[1];
$obj = new GenSalary();
$obj->execute($month);

class GenSalary {

    private $_dbCtrl;
    private $_dbCtrlIn;

    public function __construct() {

        $idc = Bd_Conf::getConf('idc/cur');
        if ($idc == 'yun') {
            $this->_dbCtrl   = Hk_Service_Db::getDB('flipped/zyb_flipped');
            $this->_dbCtrlIn = $this->_dbCtrl;
        } else {
            $this->_dbCtrl   = Hk_Service_Db::getDB('flipped/zyb_flipped_online');
            $this->_dbCtrlIn = Hk_Service_Db::getDB('flipped/zyb_flipped');
        }
    }

    public function execute($month)
    {
        // 续费周期
        $continueCycle = 7;
        if (!empty($month)) {
            $nowMonth      = $month;
            $nowMonth      = $nowMonth.'01';
            $nowTime = strtotime("$nowMonth");
            $lastMonthStartTime = strtotime(date('Y-m-01', $nowTime) . " -1 month");
            // 上上月结课并开始续报周期
            $continueStartTime  = strtotime(date('Y-m-d', $lastMonthStartTime) . "-$continueCycle day");
            $LastFirstDay = date('Y-m-01 23:59:59', $lastMonthStartTime);
            $lastMonthStopTime = strtotime("$LastFirstDay +1 month -1 day");
            // 上月结课并结束续报周期
            $continueStopTime  = strtotime(date('Y-m-d 23:59:59', $lastMonthStopTime) . "-$continueCycle day");
            // 押金月
            $depositMonth = date('Ym', strtotime(date('Ymd', $nowTime) . '-2 month'));
        } else {
            $lastMonthStartTime = strtotime(date('Y-m-01') . '-1 month');
            // 上上月结课并开始续报周期
            $continueStartTime  = strtotime(date('Y-m-d', $lastMonthStartTime) . "-$continueCycle day");
            $lastMonthStopTime  = strtotime(date('Y-m-01 23:59:59') . '-1 day');
            // 上月结课并结束续报周期
            $continueStopTime  = strtotime(date('Y-m-01 23:59:59') . "-$continueCycle day");
            // 押金月
            $depositMonth = date('Ym', strtotime(date('Ymd') . '-2 month'));
        }

        // 计薪月份
        $salaryMonth = date('Ym', $lastMonthStartTime);

        $arrTeacherInfo = array();

        // 本月结课的班级
        $sql = "select * from tblClassInfo where live_stop >= {$continueStartTime} and live_stop <= {$continueStopTime} and deleted = 0 and teacher_uid != 0 and eeo_course_id > 0";
        $classInfoList = $this->_dbCtrl->query($sql);
        foreach ($classInfoList as $classInfo) {
            $teacherUid = $classInfo['teacher_uid'];
            $classId    = $classInfo['class_id'];
            $productId  = $classInfo['product_id'];

            // 属于本老师的本班级直播课
            $idx = intval($productId/3000);
            $sql = "select * from tblLive".$idx." where class_id = {$classId} and teacher_uid = {$teacherUid} and deleted = 0";
            $liveList = $this->_dbCtrl->query($sql);
            $liveLength = 0;
            $liveCnt = 0;
            foreach ($liveList as $liveInfo) {
                $liveCnt++;
                $liveLength += intval($liveInfo['live_stop'] - $liveInfo['live_start']);
            }

            // 本班续报情况
            $sql = "select * from tblContinue where class_id = {$classId} and deleted = 0";
            $continueList = $this->_dbCtrl->query($sql);
            $arrContinueInfo = array();
            $continueCnt = 0;
            foreach ($continueList as $continueInfo) {
                $continueCnt++;
                $phone = $continueInfo['phone'];
                $arrContinueInfo[$phone][] = array(
                    'orderId'  => $continueInfo['order_id'],
                );
            }
            $arrTeacherInfo[$teacherUid]['classInfo'][$classId] = array(
                'classId'       => $classId,
                'productId'     => $productId,
                'liveLength'    => $liveLength,
                'liveCnt'       => $liveCnt,
                'studentCnt'    => $classInfo['student_cnt'],
                'continueCnt'   => $continueCnt, // 续费人次
                'continueUniCnt'  => count($arrContinueInfo), // 续费滤重人数
                'finalContinueCnt'=> $continueCnt, // 后期退费用，最终实际续费人次
                'continueInfo'  => $arrContinueInfo,
            );
        }

        // 代课 结课发生在本月的
        $substituteList = array();
        for ($i=0; $i<Fz_Ds_Live::TABLE_NUM; $i++) {
            $sql = "select a.teacher_uid,a.class_id,a.unit_id from tblLive0 as a, tblClassInfo as b where a.live_stop >= {$lastMonthStartTime} and a.live_stop <= {$lastMonthStopTime} and a.eeo_class_id > 0 and a.deleted = 0 and a.class_id = b.class_id and a.teacher_uid != b.teacher_uid";
            $list = $this->_dbCtrl->query($sql);
            $substituteList = array_merge($substituteList, $list);
        }
        foreach ($substituteList as $substituteInfo) {
            $teacherUid = $substituteInfo['teacher_uid'];
            $classId    = $substituteInfo['class_id'];
            $unitId     = $substituteInfo['unit_id'];
            $arrTeacherInfo[$teacherUid]['substituteInfo'][$classId]['unitId'][] = $unitId;
        }

        // 续费退款 本月退款的订单 -> classId -> 扣减
        $refundOrderList = array();
        for ($i=0; $i<Fz_Ds_YZOrder::TABLE_NUM; $i++) {
            $sql = "select order_id,phone from tblYZOrder".$i." where status = 3 and update_time >= {$lastMonthStartTime} and update_time <= {$lastMonthStopTime} and source_id in(1,3)";
            $list = $this->_dbCtrl->query($sql);
            $refundOrderList = array_merge($refundOrderList, $list);
        }
        foreach ($refundOrderList as $refundInfo) {
            $orderId = $refundInfo['order_id'];
            $sql = "select teacher_uid,class_id from tblContinue where order_id = {$orderId} and in_cycle = 1 and deleted = 1 and teacher_uid > 0";
            $continueInfo = $this->_dbCtrl->query($sql);
            if (!empty($continueInfo)) {
                $teacherUid = $continueInfo[0]['teacher_uid'];
                $classId    = $continueInfo[0]['class_id'];
                $sql = "select * from tblClassInfo where class_id = {$classId}";
                $classInfo = $this->_dbCtrl->query($sql);
                $liveStop = $classInfo[0]['live_stop'];
                $liveStop = $liveStop + $continueCycle*86400;
                $liveStopMonth = date('Ym', $liveStop);
                if ($salaryMonth > $liveStopMonth) {
                    if (!in_array($classId, $arrTeacherInfo[$teacherUid]['refundClassId'][$liveStopMonth])) {
                        $arrTeacherInfo[$teacherUid]['refundClassId'][$liveStopMonth][] = $classId;
                        $arrTeacherInfo[$teacherUid]['refundInfo'][$liveStopMonth][$classId] = array(
                            'classId'    => $classId,
                        );
                    }
                }
            }
        }

        // 薪资规则
        $sql = "select * from tblSalaryRule";
        $ruleList = $this->_dbCtrl->query($sql);
        // 课时 & 续费 & 代课 规则
        $arrClassContinueRuleInfo = array();
        // 押金比例 规则
        $arrDepositRuleInfo = array();
        foreach ($ruleList as $ruleInfo) {
            $teacherNature = $ruleInfo['teacher_nature'];
            $productNature = $ruleInfo['product_nature'];
            $fullClassCnt  = $ruleInfo['full_class_cnt'];
            $detail = json_decode($ruleInfo['rule_detail'], true);
            $detail = MisFz_Comm::getNewKeyArray($detail, 'continueCnt');
            $arrClassContinueRuleInfo[$teacherNature][$productNature][$fullClassCnt] = array(
                'ruleDetail'       => $detail,
                'substituteReward' => $ruleInfo['substitute_reward'],
            );
            $arrDepositRuleInfo[$teacherNature] = array(
                'depositRatio'  => $ruleInfo['deposit_ratio'],
            );
        }

        // 上期有押金老师
        $sql = "select teacher_uid,deposit from tblSalary where month = {$depositMonth} and status = 1";
        $arrLastDeposit = $this->_dbCtrlIn->query($sql);
        foreach ($arrLastDeposit as $lastDeposit) {
            $teacherUid = $lastDeposit['teacher_uid'];
            $deposit    = $lastDeposit['deposit'];
            $arrTeacherInfo[$teacherUid]['lastDeposit'] = $deposit;
        }

        // 开始计算
        foreach ($arrTeacherInfo as $teacherUid => $teacherSalaryInfo) {

            // 开启事务
            $this->_dbCtrlIn->startTransaction();

            $sql = "select nature,teacher_name,phone,status from tblTeacher where teacher_uid = {$teacherUid}";
            $teacherInfo = $this->_dbCtrl->query($sql);
            $teacherNature = $teacherInfo[0]['nature'];
            $teacherName   = $teacherInfo[0]['teacher_name'];
            $teacherPhone  = $teacherInfo[0]['phone'];

            // 课时，续费收入
            $classIncome = 0;
            $continueIncome = 0;
            $arrClassInfoList = $teacherSalaryInfo['classInfo'];
            foreach ($arrClassInfoList as $classId => $classInfo) {
                $productId = $classInfo['productId'];
                $liveCnt     = $classInfo['liveCnt'];
                $studentCnt  = $classInfo['studentCnt'];
                $continueCnt = $classInfo['continueCnt'];
                $realContinueStuCnt = $classInfo['continueUniCnt'];

                // 商品信息
                $sql = "select nature,full_class_cnt from tblProduct where product_id = {$productId}";
                $productInfo = $this->_dbCtrl->query($sql);
                $productNature = $productInfo[0]['nature'];
                $fullClassCnt  = $productInfo[0]['full_class_cnt'];

                $arrTeacherInfo[$teacherUid]['classInfo'][$classId]['productNature'] = $productNature;
                $arrTeacherInfo[$teacherUid]['classInfo'][$classId]['fullClassCnt']  = $fullClassCnt;
                $arrTeacherInfo[$teacherUid]['classInfo'][$classId]['teacherNature'] = $teacherNature;

                $ruleInfo = $arrClassContinueRuleInfo[$teacherNature][$productNature][$fullClassCnt];
                if (empty($ruleInfo)) {
                    $content = "课时费找不到薪资规则[classId]$classId [teacherUid]$teacherUid [teacherNature]$teacherNature [productNature]$productNature [fullClassCnt]$fullClassCnt";
                    Bd_Log::warning('薪资计算：'.$content);
                    self::sendMail($content);
                    continue;
                }
                $ruleDetail = $ruleInfo['ruleDetail'];

                // 续费人数（去重后）小于班级人数
                if ($realContinueStuCnt < $studentCnt) {
                    // 续费人数（非去重）小于等于 班级人数
                    if ($continueCnt <= $studentCnt) {
                        $baseClass    = $ruleDetail[$continueCnt]['baseClass'];
                        $baseContinue = $ruleDetail[$continueCnt]['baseContinue'];
                    } else {
                        $baseClass    = $ruleDetail[$studentCnt]['baseClass'];
                        $baseContinue = $ruleDetail[$studentCnt]['baseContinue'];
                    }
                } else {
                    $baseClass    = $ruleDetail[$studentCnt]['baseClass'];
                    $baseContinue = $ruleDetail[$studentCnt]['baseContinue'];
                }
                if (empty($baseClass) && intval($baseClass) !== 0) {
                    $content = "课时费找不到薪资规则 [classId]$classId [teacherUid]$teacherUid [teacherNature]$teacherNature [productNature]$productNature [fullClassCnt]$fullClassCnt [realContinueCnt]$realContinueStuCnt [continueCnt]$continueCnt [studentCnt]$studentCnt";
                    Bd_Log::warning('薪资计算：'.$content);
                    self::sendMail($content);
                    continue;
                }

                // 本班收入
                $baseIncome         = $liveCnt*$baseClass;
                $baseContinueIncome = $continueCnt*$baseContinue;
                // 累计收入
                $classIncome    += $baseIncome;
                $continueIncome += $baseContinueIncome;
                // 本班收入详情
                $arrTeacherInfo[$teacherUid]['classInfo'][$classId]['baseClass']  = $baseClass;
                $arrTeacherInfo[$teacherUid]['classInfo'][$classId]['baseIncome'] = $baseIncome;
                $arrTeacherInfo[$teacherUid]['classInfo'][$classId]['baseContinue'] = $baseContinue;
                $arrTeacherInfo[$teacherUid]['classInfo'][$classId]['baseContinueIncome'] = $baseContinueIncome;
//                if ($continueCnt > 0) {
                    $arrTeacherInfo[$teacherUid]['classInfo'][$classId]['ruleDetail'] = $ruleDetail;
//                }
            }
            // 本月收入
            $arrTeacherInfo[$teacherUid]['baseIncome'] = $classIncome;
            $arrTeacherInfo[$teacherUid]['baseContinueIncome'] = $continueIncome;

            // 代课收入
            $substituteIncome = 0;
            $arrSubstituteInfo = $teacherSalaryInfo['substituteInfo'];
            foreach ($arrSubstituteInfo as $classId => $classSudInfo) {
                $sql = "select product_id from tblClassInfo where class_id = {$classId}";
                $classInfo = $this->_dbCtrl->query($sql);
                $productId = $classInfo[0]['product_id'];
                // 商品信息
                $sql = "select nature,full_class_cnt from tblProduct where product_id = {$productId}";
                $productInfo = $this->_dbCtrl->query($sql);
                $productNature = $productInfo[0]['nature'];
                $fullClassCnt  = $productInfo[0]['full_class_cnt'];

                $ruleInfo = $arrClassContinueRuleInfo[$teacherNature][$productNature][$fullClassCnt];
                if (empty($ruleInfo)) {
                    $content = "代课费找不到薪资规则[classId]$classId [teacherUid]$teacherUid [teacherNature]$teacherNature [productNature]$productNature [fullClassCnt]$fullClassCnt";
                    Bd_Log::warning('薪资计算：'.$content);
                    self::sendMail($content);
                    continue;
                }
                $substituteReward = $ruleInfo['substituteReward'] ? $ruleInfo['substituteReward'] : 0;
                $classSubIncome = count($classSudInfo['unitId'])*$substituteReward;
                $substituteIncome += $classSubIncome;
                $arrTeacherInfo[$teacherUid]['substituteInfo'][$classId]['substituteIncome'] = $classSubIncome;
                $arrTeacherInfo[$teacherUid]['substituteInfo'][$classId]['substituteReward'] = $substituteReward;
            }
            $arrTeacherInfo[$teacherUid]['substituteIncome'] = $substituteIncome;

            // 续报扣费
            $refundFine = 0;
            $arrRefundInfo = $teacherSalaryInfo['refundInfo'];
            foreach ($arrRefundInfo as $month => $arrClassId) {
                foreach ($arrClassId as $classId) {
                    $classId = $classId['classId'];
                    $sql = "select ext_data from tblSalary where month = {$month} and teacher_uid = {$teacherUid}";
                    $salaryInfo = $this->_dbCtrlIn->query($sql);
                    $extSalary = $salaryInfo[0]['ext_data'];
                    $extSalary = json_decode($extSalary, true);
                    $classSalaryInfo  = $extSalary['classInfo'][$classId];
                    if (empty($classSalaryInfo)) {
                        $content = "续报退款找不到班级结算数据[sql]$sql [classId]$classId ";
                        Bd_Log::warning('薪资计算：'.$content);
                        self::sendMail($content);
                        continue;
                    }
                    $finalContinueCnt = $classSalaryInfo['finalContinueCnt'];
                    $liveCnt          = $classSalaryInfo['liveCnt'];
                    $classStudentCnt  = $classSalaryInfo['studentCnt'];
                    $ruleDetail = $classSalaryInfo['ruleDetail'];

                    $sql = "select * from tblContinue where class_id = {$classId} and deleted = 0";
                    $continueList = $this->_dbCtrl->query($sql);
                    $arrContinueInfo = array();
                    $continueCnt = 0;
                    foreach ($continueList as $continueInfo) {
                        $continueCnt++;
                        $phone = $continueInfo['phone'];
                        $arrContinueInfo[$phone][] = $continueInfo['order_id'];
                    }
                    $realContinueStuCnt = count($arrContinueInfo); // 续费滤重人数
                    // 续费人数（去重后）小于班级人数
                    if ($realContinueStuCnt < $classStudentCnt) {
                        // 续费人数（非去重）小于等于 班级人数
                        if ($continueCnt <= $classStudentCnt) {
                            $baseClass    = $ruleDetail[$continueCnt]['baseClass'];
                            $baseContinue = $ruleDetail[$continueCnt]['baseContinue'];
                        } else {
                            $baseClass    = $ruleDetail[$classStudentCnt]['baseClass'];
                            $baseContinue = $ruleDetail[$classStudentCnt]['baseContinue'];
                        }
                    } else {
                        $baseClass    = $ruleDetail[$classStudentCnt]['baseClass'];
                        $baseContinue = $ruleDetail[$classStudentCnt]['baseContinue'];
                    }
                    // 本班课时&续费扣费
                    $baseFine         = $liveCnt*($classSalaryInfo['baseClass'] - $baseClass);
                    $baseContinueFine = $finalContinueCnt*$classSalaryInfo['baseContinue'] - $baseContinue*$continueCnt;
                    $arrTeacherInfo[$teacherUid]['refundInfo'][$month][$classId]['lastContinueCnt'] = $finalContinueCnt;
                    $arrTeacherInfo[$teacherUid]['refundInfo'][$month][$classId]['lastBaseClass'] = $classSalaryInfo['baseClass'];
                    $arrTeacherInfo[$teacherUid]['refundInfo'][$month][$classId]['lastBaseContinue'] = $classSalaryInfo['baseContinue'];
                    $arrTeacherInfo[$teacherUid]['refundInfo'][$month][$classId]['finalContinueCnt'] = $continueCnt;
                    $arrTeacherInfo[$teacherUid]['refundInfo'][$month][$classId]['liveCnt']  = $liveCnt;
                    $arrTeacherInfo[$teacherUid]['refundInfo'][$month][$classId]['baseClass'] = $baseClass;
                    $arrTeacherInfo[$teacherUid]['refundInfo'][$month][$classId]['baseFine'] = $baseFine;
                    $arrTeacherInfo[$teacherUid]['refundInfo'][$month][$classId]['baseContinue'] = $baseContinue;
                    $arrTeacherInfo[$teacherUid]['refundInfo'][$month][$classId]['baseContinueFine'] = $baseContinueFine;
                    $arrTeacherInfo[$teacherUid]['refundInfo'][$month][$classId]['finalFine'] = $baseFine + $baseContinueFine;

                    // 累计退费扣款
                    $refundFine += $baseFine + $baseContinueFine;

                    // 更新退费班级最终续费人次
                    $extSalary['classInfo'][$classId]['finalContinueCnt'] = $continueCnt;
                    $extSalary = bin2hex(json_encode($extSalary));
                    $sql = "update tblSalary set ext_data = unhex('$extSalary') where month = {$month} and teacher_uid = {$teacherUid}";
                    $retSalary = $this->_dbCtrlIn->query($sql);
                    if (false === $retSalary) {
                        $this->_dbCtrlIn->rollback();
                        $content = "更新班级退费扣款操作[sql]$sql";
                        Bd_Log::warning('薪资计算：'.$content);
                        self::sendMail($content);
                        continue;
                    }
                }
            }

            // 上期押金
            $lastDeposit = !empty($teacherSalaryInfo['lastDeposit']) ? $teacherSalaryInfo['lastDeposit'] : 0;

            // 押金比例
            $depositRatio = $arrDepositRuleInfo[$teacherNature]['depositRatio'];

            // 按分计算
            $classIncome = $classIncome * 100;
            $continueIncome = $continueIncome * 100;
            $substituteIncome = $substituteIncome * 100;
            $refundFine = $refundFine * 100;

            // 总计
            $total = $classIncome + $continueIncome + $substituteIncome - $refundFine;

            // 本期押金
            $deposit = sprintf('%.2f', $total * $depositRatio / 100)*100;

            // 实发
            $payroll = $total - $deposit + $lastDeposit;

            $salaryExt = bin2hex(json_encode($arrTeacherInfo[$teacherUid]));

            // 计算薪资时间
            $time = time();

            $sql = "select * from tblSalary where month = {$salaryMonth} and teacher_uid = {$teacherUid}";
            $ret = $this->_dbCtrlIn->query($sql);
            // 计算最终sql
            if (empty($ret)) {
                $sql = "insert into tblSalary(
                  teacher_uid,teacher_name,phone,month,class_income,continue_income,refund_fine,substitute_income,last_deposit,deposit_ratio,deposit,payroll,status,create_time,ext_data)
                  values($teacherUid,'$teacherName',$teacherPhone,$salaryMonth,$classIncome,$continueIncome,$refundFine,$substituteIncome,$lastDeposit,$depositRatio,$deposit,$payroll,0,$time,unhex('$salaryExt'))";
            } else {
                $sql = "update tblSalary set class_income = {$classIncome}, continue_income = {$continueIncome}, refund_fine = {$refundFine}, substitute_income = {$substituteIncome}, last_deposit = {$lastDeposit}, deposit_ratio = {$depositRatio}, deposit = {$deposit}, payroll = {$payroll}, status = 0, ext_data = unhex('$salaryExt')
                        where teacher_uid = {$teacherUid} and month = {$salaryMonth}";
            }
            $ret = $this->_dbCtrlIn->query($sql);
            if (false === $ret) {
                $this->_dbCtrlIn->rollback();
                $content = "新增薪资计算结果操作[sql]$sql";
                Bd_Log::warning('薪资计算：'.$content);
                self::sendMail($content);
                continue;
            }

            // 提交事务
            $this->_dbCtrlIn->commit();
        }

    }

    private static function sendMail($content) {
        // 邮件报警
        $idc = Bd_Conf::getConf('idc/cur');
        if ($idc == 'yun') {
            $subject = '【翻转 - 薪酬计算】';
            Hk_Util_Mail::sendMail('shaohuan@zuoyebang.com', $subject, $content);
        }
    }
}