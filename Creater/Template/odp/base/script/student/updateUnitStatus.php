<?php
/**
 * @file    updateUnitStatus.php
 * @author  zhaojinsong(zhaojinsong@zuoyebang.com)
 * @date    2018-02-12
 * @brief   根据直播表更新学生单元状态
 */
//！！！！！！！！！！！！改成在直播开始半小时后更新
Bd_Init::init('misfz');

//增加文件锁
$lockFile = './updateUnitStatus.lock';

file_exists($lockFile) || touch($lockFile);

$fp = fopen($lockFile, 'r+');

if (!$fp) {
    exit;
}
if (flock($fp, LOCK_EX | LOCK_NB)) {
    $objDaoAccount = new Fz_Dao_Account();
    $objDsClassInfo = new Fz_Ds_ClassInfo();
    $objDsClassStudent = new Fz_Ds_ClassStudent();
    $objDsStudentUnit = new Fz_Ds_StudentUnit();

// 1. 从Live表中查询直播开始半小时的记录，class_id、live_start、unitId
// 2. 根据classId从classInfo表中查询courseId、skuId
// 3. 从classLive表中studentUid列表
// 4. 根据studentUid列表、courseId、unitId更新studentUnit表的状态

//错误记录
    $arrFaildList = array();

    $adminList = array(
        'zhaojinsong@zuoyebang.com', //赵劲松
    );


//查询已经开始30-35分钟的直播
    $curTime = time();
    $startTime = $curTime - 60 * 35;
    $endTime = $curTime - 60 * 30;
    $maxNum = 1;

    for ($i = 0; $i < $maxNum; $i++) {
        $sql = "select id,class_id,unit_id,bit_map,product_id from tblLive" . $i . " where live_start >= $startTime and live_start <= $endTime and deleted = 0";
        $arrClassLive = $objDaoAccount->query($sql);
        if (false === $arrClassLive) {
            $arrFaildList[] = "查询classLive{$i}失败";
            continue;
        }
        if (empty($arrClassLive)) {
            continue;
        }
        foreach ($arrClassLive as $classLive) {
            $id = intval($classLive['id']);
            $classId = intval($classLive['class_id']);
            $curUnitId = intval($classLive['unit_id']);
            $bitMap = intval($classLive['bit_map']);
            $productId = intval($classLive['product_id']);
            if ($bitMap & 1) {
                //处理过，跳过
                continue;
            }
            $classInfo = $objDsClassInfo->getClassInfo($classId);
            if (false === $classInfo) {
                $arrFaildList[] = "获取classInfo fail，classId:$classId";
                continue;
            }
            if (empty($classInfo)) {
                $arrFaildList[] = "classInfo数据不存在，classId:$classId";
                continue;
            }
            $courseId = intval($classInfo['courseId']);

            //获取rank对应的unitId的下一单元unitId
            $sql = "select unit_list from tblCourse where course_id = $courseId";
            $courseInfo = $objDaoAccount->query($sql);
            if (false === $courseInfo) {
                $arrFaildList[] = "查询tblCourse失败，course_id:$courseId";
                continue;
            }
            if (empty($courseInfo)) {
                $arrFaildList[] = "查询tblCourse为空，course_id:$courseId";
                continue;
            }
            //判断是否为最后一单元，找到下一单元
            $arrUnitId = json_decode($courseInfo[0]['unit_list'], true);
            $intUnitNum = count($arrUnitId);
            for ($num = 0; $num < $intUnitNum; $num++) {
                if ($arrUnitId[$num] == $curUnitId) {
                    break;
                }
            }
            if ($num == $intUnitNum - 1) {
                continue;
            }
            $nextUnitId = $arrUnitId[$num+1];

            $sql = "select student_uid,phone from tblClassStudent" . $i . " where class_id = $classId and deleted = 0";
            $arrClassStudent = $objDaoAccount->query($sql);
            if (false === $arrClassStudent) {
                $arrFaildList[] = "查询tblClassStudent{$i}失败，classId:$classId";
                continue;
            }
            if (empty($arrClassStudent)) {
                $arrFaildList[] = "查询tblClassStudent{$i}为空，classId:$classId";
                continue;
            }
            //$objDaoAccount->startTransaction();
            //$flag = 0;
            foreach ($arrClassStudent as $classStudent) {
                $studentUid = intval($classStudent['student_uid']);
                $phone = intval($classStudent['phone']);
                $ret = $objDsStudentUnit->modifyUnitStatus($studentUid, $productId,$courseId, $curUnitId, Fz_Ds_StudentUnit::STATUS_DONE);
                if (false === $ret) {
                    $arrFaildList[] = "更新studentUnit失败,studentUid:$studentUid,productId:$productId,courseId:$courseId,unitId:$curUnitId,phone:$phone";
                    //$flag = 1;
                    continue;
                    //break;
                }
                $ret = $objDsStudentUnit->modifyUnitStatus($studentUid, $productId, $courseId, $nextUnitId, Fz_Ds_StudentUnit::STATUS_DOING);
                if (false === $ret) {
                    $arrFaildList[] = "更新studentUnit失败,studentUid:$studentUid,productId:$productId,courseId:$courseId,unitId:$nextUnitId,phone:$phone";
                    //$flag = 1;
                    //break;
                    continue;
                }
            }
//            if ($flag) {
//                $objDaoAccount->rollback();
//                continue;
//            }

            $newBitMap = $bitMap | 1;
            //更新Live的bitMap
            $sql = "update tblLive" . $i . " set bit_map = $newBitMap where id = $id";
            $ret = $objDaoAccount->query($sql);
            if (false === $ret) {
                $arrFaildList[] = "tblLive{$i}失败,classId:$classId,id:$id";
                //$objDaoAccount->rollback();
                //continue;
            }
            //$objDaoAccount->commit();

        }

    }


    sendEmail($arrFaildList, $adminList);
}else {
    echo "任务冲突" . PHP_EOL;
}

fclose($fp);

function sendEmail($arrFailedList, $adminList) {
    //发送邮件
    if (!empty($arrFailedList) ) {

        $message = "<table>";
        $message .= "<tr><td>reason</td></tr>";
        foreach ($arrFailedList as $reason) {
            $message .= "<tr>";
            $message .= "<td>" . $reason . "</td>";
            $message .= "</tr>";
        }
        $message .= "</table>";

        $subject = '【自动邮件】- 直播结束更新学生单元状态失败通知';

        $toMail = implode(",", $adminList);

        exec("curl http://proxy.zuoyebang.com:1925/api/mail -XPOST -d 'tos=$toMail&subject=$subject&content=$message&format=html'");
    }
}