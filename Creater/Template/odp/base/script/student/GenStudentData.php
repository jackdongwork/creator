<?php
/**
 * @file    GenStudentData.php
 * @author  zhaojinsong(zhaojinsong@zuoyebang.com)
 * @date    2018-02-08
 * @brief   根据订单生成用户数据
 */
Bd_Init::init('misfz');

//增加文件锁
$lockFile = './GenStudentData.lock';

file_exists($lockFile) || touch($lockFile);

$fp = fopen($lockFile, 'r+');

if (!$fp) {
    exit;
}
if (flock($fp, LOCK_EX | LOCK_NB)) {

    $objDaoAccount = new Fz_Dao_Account();
    $objDsStudentCourse = new Fz_Ds_StudentCourse();
    $objDsStudentUnit = new Fz_Ds_StudentUnit();
    $objDsStudentExam = new Fz_Ds_StudentExam();
    $objDsAccount = new Fz_Ds_Account();
    $objDsEEOAccount = new Fz_Ds_EEOAccount();
//错误记录
    $arrFailedList = array();

    $adminList = array(
        'zhaojinsong@zuoyebang.com', //赵劲松
    );

//每次拉取最近五分钟的订单
    $curTime = time();
    $startTime = $curTime - 900;

    $sql = "select product_id,course_id,student_uid,phone,sku_id,ext_data from tblYZOrder0 where status = 1 and deleted = 0 and pay_time >= $startTime and pay_time <= $curTime and  student_uid != 0";
    $arrOrderList = $objDaoAccount->query($sql);
    if (!empty($arrOrderList)) {
        foreach ($arrOrderList as $orderDetail) {
            $productId = intval($orderDetail['product_id']);
            $courseId = intval($orderDetail['course_id']);
            $studentUid = intval($orderDetail['student_uid']);
            $skuId = intval($orderDetail['sku_id']);
            $phone = intval($orderDetail['phone']);
            if (empty($productId) || empty($courseId) || empty($skuId)) {
                $arrFailedList[] = "订单数据异常，studentUid:$studentUid,courseId:$courseId,productId:$productId,skuId:$skuId";
                continue;
            }
            $extData = isset($orderDetail['ext_data']) ? json_decode($orderDetail['ext_data'], true) : array();
            $classId = intval($extData['classId']);
            if (intval($extData['changeCourse']) === 1) {
                //用户调课
                $courseId = intval($extData['courseId']);
                $productId = intval($extData['productId']);
                $skuId = intval($extData['skuId']);
            } elseif (intval($extData['changeClass']) === 1) {
                //用户调班
                $skuId = intval($extData['skuId']);
            }

            //1. 判断是否已经写入过studentCourse
            $ret = $objDsStudentCourse->getRecordByStudentUidAndCourseId($studentUid, $productId, $courseId);
            if (false === $ret) {
                $arrFailedList[] = "判断是否写入过studentCourse失败，studentUid:$studentUid,courseId:$courseId,productId:$productId";
                continue;
            }
            if (!empty($ret)) {
                //已经写过
                continue;
            }

            $objDaoAccount->startTransaction();

            //2. 向studentCourse表写入记录
            $ret = $objDsStudentCourse->addStudentCourse(array(
                'studentUid' => $studentUid,
                'courseId' => $courseId,
                'productId' => $productId,
                'skuId' => $skuId,
                'classId' => $classId,
            ));
            if (false === $ret) {
                $arrFailedList[] = "写入studentCourse失败，studentUid:$studentUid,courseId:$courseId,productId:$productId";
                $objDaoAccount->rollback();
                continue;
            }

            //3. 向studentUnit写入记录
            //3.1 查询course对应的unit
            $sql = "select unit_list from tblCourse where course_id = $courseId";
            $arrUnitList = $objDaoAccount->query($sql);
            if (false === $arrUnitList) {
                $arrFailedList[] = "查询course对应的unit失败，studentUid:$studentUid,courseId:$courseId,productId:$productId";
                $objDaoAccount->rollback();
                continue;
            }
            $arrUnitList = $arrUnitList[0]['unit_list'];
            $arrUnitList = json_decode($arrUnitList, true);
            //3.2 插入第一个unit，状态为进行中
            $firUnitId = $arrUnitList[0];
            $ret = $objDsStudentUnit->addStudentUnit(
                array(
                    'studentUid' => $studentUid,
                    'unitId' => $firUnitId,
                    'courseId' => $courseId,
                    'status' => Fz_Ds_StudentUnit::STATUS_DOING,
                    'productId' => $productId,
                )
            );
            if (false === $ret) {
                $arrFailedList[] = "插入第一个unit失败，studentUid:$studentUid,courseId:$courseId,productId:$productId,unitId:$firUnitId";
                $objDaoAccount->rollback();
                continue;
            }

            //3.3 为后续的单元插入记录
            if (count($arrUnitList) > 1) {
                $flag = 0;
                for ($i = 1; $i < count($arrUnitList); $i++ ) {
                    $ret = $objDsStudentUnit->addStudentUnit(
                        array(
                            'studentUid' => $studentUid,
                            'unitId' => $arrUnitList[$i],
                            'courseId' => $courseId,
                            'status' => Fz_Ds_StudentUnit::STATUS_UNSTART,
                            'productId' => $productId,
                        )
                    );
                    if (false === $ret) {
                        $arrFailedList[] = "插入第{$i}unit失败，studentUid:$studentUid,courseId:$courseId,productId:$productId,unitId:{$arrUnitList[$i]}";
                        $flag = 1;
                        break;
                    }
                }
                if ($flag) {
                    $objDaoAccount->rollback();
                    continue;
                }
            }

            // 4. 分配classIn账号
            $accountInfo = $objDsAccount->getAccountByUid($studentUid);
            if (false === $accountInfo) {
                $arrFailedList[] = "查询tblAccount失败，studentUid:$studentUid,courseId:$courseId,productId:$productId,unitList:" . implode(',', $arrUnitList);
                $objDaoAccount->rollback();
                continue;
            }
            if (!empty($accountInfo)) {
                $ret = $objDsAccount->distributeEEOAccount($studentUid, $phone);
                if (false === $ret) {
                    $arrFailedList[] = "分配classIn账号失败，studentUid:$studentUid,courseId:$courseId,productId:$productId,unitList:" . implode(',', $arrUnitList);
                    $objDaoAccount->rollback();
                    continue;
                }
            }

            $objDaoAccount->commit();
        }
    }

//发送邮件
    if (!empty($arrFailedList)) {

        $message = "<table>";
        $message .= "<tr><td>reason</td></tr>";
        foreach ($arrFailedList as $reason) {
            $message .= "<tr>";
            $message .= "<td>" . $reason . "</td>";
            $message .= "</tr>";
        }
        $message .= "</table>";

        $subject = '【自动邮件】- 同步订单生成用户数据失败';

        $toMail = implode(",", $adminList);

        exec("curl http://proxy.zuoyebang.com:1925/api/mail -XPOST -d 'tos=$toMail&subject=$subject&content=$message&format=html'");
    }
}else {
    echo "任务冲突" . PHP_EOL;
}

fclose($fp);