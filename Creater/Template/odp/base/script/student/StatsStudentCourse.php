<?php
/**
 * @file    StatsStudentCourse.php
 * @author  zhaojinsong
 * @date    2018-05-29
 * @brief   统计报家长课用户第一次上课的时间（zyb）、报正式课的用户uid(hx)
 */

Bd_Init::init('misfz');

$zybDbName = "fudao/zyb_fudao";
$zybDbCtrl = Hk_Service_Db::getDB($zybDbName);

$hxDbName = "flipped/zyb_flipped";
$hxDbCtrl = Hk_Service_Db::getDB($hxDbName);

$redisConf  = Bd_Conf::getConf("/hk/redis/common");
$objRedis   = new Hk_Service_Redis($redisConf['service']);

$key_zyb_course = "KEY_MISFZ_ZYB_COURSE";
$key_zyb = 'KEY_MISFZ_ZYB_FIRST_ATTEND_UID_'; //报名家长课用户第一次上课的时间
$key_hx = 'KEY_MISFZ_HX_REPORT_UID_'; //报正式课的uid
$key_hx_start_time = 'KEY_MISFZ_HX_START_TIME';
$key_expire = 86400 * 7;

//第一次上课的uid=>time列表
$arrFirstAttendUid = array();
for ($i = 0; $i < 20; $i++) {
    $ret = $objRedis->get($key_zyb . $i);
    $arrFirstAttendUid[$i] = empty($ret) ? array() : json_decode($ret, true);
}

//已经处理的courseId
$ret = $objRedis->get($key_zyb_course);
$arrAlreadyHandleCourseId = empty($ret) ? array() : json_decode($ret, true);

//老师uid
$arrTeacherUid = array(2282911378, 2281664450, 2280368723, 2180913046, 2267514968, 2177636272);

$sql = "select course_id from tblTeacherCourse where teacher_uid in (". implode(',', $arrTeacherUid) .")";

$arrCourse = $zybDbCtrl->query($sql);
if (false === $arrCourse) {
    return;
}

if (!empty($arrCourse)) {
    //待处理的courseId
    $arrCourseId = array();
    foreach ($arrCourse as $course) {
        $courseId = intval($course['course_id']);
        $arrCourseId[] = $courseId;
    }

    //剔除已经处理的courseId
    $arrCourseId = array_diff($arrCourseId, $arrAlreadyHandleCourseId);

    $sql = "select course_id,online_start,online_stop from tblCourse where course_id in (". implode(',', $arrCourseId) .")";
    $ret = $zybDbCtrl->query($sql);
    if (false === $ret) {
        return;
    }
    if (empty($ret)) {
        return;
    }
    //课程id => 开始时间
    $arrCourse = array();
    $arrTmp = array();
    foreach ($ret as $value) {
        if ($value['online_stop'] > time() ) {
            //直播未结束，不统计
            continue;
        }
        $arrTmp[] = $value['course_id'];
        $arrCourse[$value['course_id']] = intval($value['online_start']);
    }
    $arrCourseId = $arrTmp;

    $arrAlreadyHandleCourseId = array_merge($arrCourseId, $arrAlreadyHandleCourseId);

    foreach ($arrCourseId as $courseId) {
        //查询lesson
        $sql = "select lesson_id from tblLesson where course_id = $courseId";
        $ret = $zybDbCtrl->query($sql);
        if (false === $ret) {
            continue;
        }
        if (empty($ret)) {
            continue;
        }
        $lessonId = $ret[0]['lesson_id'];
        $startTime = $arrCourse[$courseId]; // 该门课开始时间
        for ($i = 0; $i < 20; $i++) {
            $sql = "select student_uid from tblStudentLesson". $i . " where lesson_id = $lessonId and first_attend_time > 0";
            $arrStudentLesson = $zybDbCtrl->query($sql);
            if (false === $arrStudentLesson) {
                return ;
            }
            if (!empty($arrStudentLesson)) {
                foreach ($arrStudentLesson as $studentLesson) {
                    $studentUid = intval($studentLesson['student_uid']);
                    if (isset($arrFirstAttendUid[$i][$studentUid])) {
                        if ($arrFirstAttendUid[$i][$studentUid] > $startTime) {
                            $arrFirstAttendUid[$i][$studentUid] = $startTime;
                        }
                    } else {
                        $arrFirstAttendUid[$i][$studentUid] = $startTime;
                    }
                }
            }
        }
    }
    //存入已处理的courseId
    $objRedis->setex($key_zyb_course, json_encode($arrAlreadyHandleCourseId), $key_expire);
    for ($i = 0; $i < 20; $i++) {
        //第一次报名家长课的uid=>time
        $objRedis->setex($key_zyb . $i, json_encode($arrFirstAttendUid[$i]), $key_expire);
    }

    unset($arrFirstAttendUid);
    unset($arrAlreadyHandleCourseId);
}



//报正式课的uid
$arrHxUid = array();
for ($i = 0; $i < 20; $i++) {
    $ret = $objRedis->get($key_hx . $i);
    $arrHxUid[$i] = empty($ret) ? array() : json_decode($ret, true);
}

//开始时间
$ret = $objRedis->get($key_hx_start_time);
$startTime = empty($ret) ? 0 : intval($ret);
$nowTime = time();


$sql = "select distinct student_uid from tblYZOrder0 where create_time > $startTime and create_time <= $nowTime and status in (1,2,3) and deleted = 0 and student_uid > 0";
$ret = $hxDbCtrl->query($sql);
if (false === $ret) {
    return ;
}

if (!empty($ret)) {
    foreach ($ret as $value) {
        $studentUid = intval($value['student_uid']);
        $mod = $studentUid % 20;
        if (!in_array($studentUid, $arrHxUid[$mod])) {
            $arrHxUid[$mod][] = $studentUid;
        }
    }
}

for ($i = 0; $i < 20; $i++) {
    $objRedis->setex($key_hx . $i , json_encode($arrHxUid[$i]), $key_expire);
}
$objRedis->setex($key_hx_start_time, $nowTime, $key_expire);

unset($arrHxUid);
unset($ret);

