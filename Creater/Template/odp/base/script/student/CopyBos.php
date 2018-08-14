<?php
/**
 * @file    CopyBos.php
 * @author  zhaojinsong(zhaojinsong@zuoyebang.com)
 * @date    2018-06-19
 * @brief   从charge拷贝资源到image
 */
Bd_Init::init('plus');
set_time_limit(0);

$objDsExam = new Fz_Ds_Exam();
$objDsAccount = new Fz_Ds_Account();
$objDsQuestion = new Fz_Ds_Question();
$objBos = new Hk_Service_Bos('image');
$srcBucket = 'zyb-charge';
$arrResult = array(
    'exam'    => array(
        'qid' => array(),
        'res' => array(),
    ),
    'course' => array(),
    'fail' => array(),
);

//所有exam的题资源
$sql = "select exam_id as examId,exam_list as examList from tblExam";
$arrExam = $objDsAccount->query($sql);

if (false === $arrExam) {
    Bd_Log::warning("query tblExam error,sql:$sql");
    $arrResult['fail'][] = '获取tblExam数据失败，sql:$sql';
    return;
}

//exam总数
$num = count($arrExam);
Bd_Log::warning("exam total num : $num");

Bd_Log::warning("copy exam start....");

if (!empty($arrExam)) {
    foreach ($arrExam as $exam) {

        $examList = json_decode($exam['examList'], true);
        $examId = intval($exam['examId']);

        Bd_Log::warning("exam $examId start....");

        if (empty($examList) || !is_array($examList)) {
            Bd_Log::warning("examList error, examId:$examId");
            continue;
        }

        foreach ($examList as $key=> $val) {
            //解析每个模块的qid
            $qidList = explode(',', $val);
            if (empty($qidList)) {
                continue;
            }

            foreach ($qidList as $qid) {
                $qInfo = $objDsQuestion->getQuesInfo($qid);
                if (empty($qInfo)) {
                    Bd_Log::warning("query tblQuestion error,qid:$qid");
                    $arrResult['fail']['qid'][] = $qid;
                    continue;
                }
                $extData = $qInfo['extData'][0];

                //宠物语音
                $petVoiceFile = $extData['petVoiceFile'];
                if (!empty($petVoiceFile)) {
//                    $ret = $objBos->copyObject($petVoiceFile, $petVoiceFile, 'zyb-charge');
                    $ret = copyRes($objBos, $petVoiceFile, $srcBucket);
                    if (false === $ret) {
                        Bd_Log::warning("copy res error... [examId:$examId,qid:$qid,res:$petVoiceFile]");
                        $arrResult['fail']['res'][] = $examId . ',' . $qid. ',' .$petVoiceFile;
                        continue;
                    } else {
                        Bd_Log::warning("copy res success... [examId:$examId,qid:$qid,res:$petVoiceFile]");
                    }
                }

                //stemVideo、stemImg、stemAudio、optionImg、screenShot、background、
                $enclosure = Fz_Ds_Question::$enclosure;
                foreach ($extData as $dk => $dv) {
                    if (in_array($dk, array_keys($enclosure))) {
                        if ($dk == 'optionAudio' || $dk == 'optionImg') {
                            continue;
                        }
                        foreach ($dv as $enk => $env) {
                            //$ret = $objBos->copyObject($env, $env, 'zyb-charge');
                            $ret = copyRes($objBos, $env, $srcBucket);
                            if (false === $ret) {
                                Bd_Log::warning("copy res error... [examId:$examId,qid:$qid,res:$env]");
                                $arrResult['fail']['res'][] = $examId . ',' . $qid. ',' .$env;
                                continue;
                            }else {
                                Bd_Log::warning("copy res success... [examId:$examId,qid:$qid,res:$env]");
                            }
                        }
                    }
                }

                //draftDesc
                $draftDescList = $extData['draftDesc'];
                if (!empty($draftDescList) && is_array($draftDescList)) {
                    foreach ($draftDescList as $draftDesc) {
                        if (!empty($draftDesc['draftDescVoice'])) {
                            //$ret = $objBos->copyObject($draftDesc['draftDescVoice'], $draftDesc['draftDescVoice'], 'zyb-charge');
                            $ret = copyRes($objBos, $draftDesc['draftDescVoice'], $srcBucket);
                            if (false === $ret) {
                                Bd_Log::warning("copy res error... [examId:$examId,qid:$qid,res:{$draftDesc['draftDescVoice']}]");
                                $arrResult['fail']['res'][] = $examId . ',' . $qid. ',' .$draftDesc['draftDescVoice'];
                                continue;
                            } else {
                                Bd_Log::warning("copy res success... [examId:$examId,qid:$qid,res:{$draftDesc['draftDescVoice']}]");
                            }
                        }
                    }
                }
                //interfereAttr
                $interfereAttrList = $extData['interfereAttr'];
                if (!empty($interfereAttrList) && is_array($interfereAttrList)) {
                    foreach ($interfereAttrList as $interfereAttr) {
                        if (!empty($interfereAttr['interfereAttrVoice'])) {
                            //$ret = $objBos->copyObject($interfereAttr['interfereAttrVoice'], $interfereAttr['interfereAttrVoice'], 'zyb-charge');
                            $ret = copyRes($objBos, $interfereAttr['interfereAttrVoice'], $srcBucket);
                            if (false === $ret) {
                                Bd_Log::warning("copy res error... [examId:$examId,qid:$qid,res:{$interfereAttr['interfereAttrVoice']}]");
                                $arrResult['fail']['res'][] = $examId . ',' . $qid. ',' .$interfereAttr['interfereAttrVoice'];
                                continue;
                            } else {
                                Bd_Log::warning("copy res success... [examId:$examId,qid:$qid,res:{$interfereAttr['interfereAttrVoice']}]");
                            }
                        }
                    }
                }

                //options
                $options = $extData['options'];
                if (!empty($options) && is_array($options)) {
                    foreach ($options as $option) {
                        $optionImg = $option['optionImg'];
                        //$ret = $objBos->copyObject($optionImg, $optionImg, 'zyb-charge');
                        $ret = copyRes($objBos, $optionImg, $srcBucket);
                        if (false === $ret) {
                            Bd_Log::warning("copy res error... [examId:$examId,qid:$qid,res:$optionImg]");
                            $arrResult['fail']['res'][] = $examId . ',' . $qid. ',' .$optionImg;
                        } else {
                            Bd_Log::warning("copy res success... [examId:$examId,qid:$qid,res:$optionImg]");
                        }
                        $optionAudio = $option['optionAudio'];
                        //$ret = $objBos->copyObject($optionAudio, $optionAudio, 'zyb-charge');
                        $ret = copyRes($objBos, $optionAudio, $srcBucket);
                        if (false === $ret) {
                            Bd_Log::warning("copy res error... [examId:$examId,qid:$qid,res:$optionAudio]");
                            $arrResult['fail']['res'][] = $examId . ',' . $qid. ',' .$optionAudio;
                        } else {
                            Bd_Log::warning("copy res success... [examId:$examId,qid:$qid,res:$optionAudio]");
                        }
                    }
                }

                //optionAudio
                $optionAudio = $extData['optionAudio'];
                if (!empty($optionAudio) && is_array($optionAudio)) {
                    foreach ($optionAudio as $pos => $img) {
                        //$ret = $objBos->copyObject($img[$pos + 1], $img[$pos + 1], 'zyb-charge');
                        $ret = copyRes($objBos, $img[$pos + 1], $srcBucket);
                        if (false === $ret) {
                            Bd_Log::warning("copy res error... [examId:$examId,qid:$qid,res:{$img[$pos + 1]}]");
                            $arrResult['fail']['res'][] = $examId . ',' . $qid. ',' .$img[$pos + 1];
                            continue;
                        } else {
                            Bd_Log::warning("copy res success... [examId:$examId,qid:$qid,res:{$img[$pos + 1]}]");
                        }
                    }
                }

                //optionImg
                $optionImg = $extData['optionImg'];
                if (!empty($optionImg) && is_array($optionImg)) {
                    foreach ($optionImg as $pos => $img) {
                        //$ret = $objBos->copyObject($img[$pos + 1], $img[$pos + 1], 'zyb-charge');
                        $ret = copyRes($objBos, $img[$pos + 1], $srcBucket);
                        if (false === $ret) {
                            Bd_Log::warning("copy res error... [examId:$examId,qid:$qid,res:{$img[$pos + 1]}]");
                            $arrResult['fail']['res'][] = $examId . ',' . $qid. ',' .$img[$pos + 1];
                            continue;
                        } else {
                            Bd_Log::warning("copy res success... [examId:$examId,qid:$qid,res:{$img[$pos + 1]}]");
                        }
                    }
                }

                //end
            }
        }
        Bd_Log::warning("exam $examId end and sleep 2 second....");
        sleep(2);
    }
}
unset($arrExam);

Bd_Log::warning("copy exam end....");

//所有course的封面
$sql = "select course_id as courseId,cover_img as coverImg from tblCourse";
$arrCourse = $objDsAccount->query($sql);
if (false === $arrCourse) {
    $arrResult['fail'][] = '获取tblCourse数据失败，sql:$sql';
    return ;
}

Bd_Log::warning("copy course start....");

if (!empty($arrCourse)) {
    foreach ($arrCourse as $course) {
        if (!empty($course['coverImg'])) {
            //$ret = $objBos->copyObject($course['coverImg'], $course['coverImg'], 'zyb-charge');
            $ret = copyRes($objBos, $course['coverImg'], $srcBucket);
            if (false === $ret) {
                Bd_Log::warning("copy res error... [courseId:{$course['courseId']},res:{$course['coverImg']}]");
                $arrResult['course'][] = $course['courseId'] . '-' .$course['coverImg'];
                continue;
            } else {
                Bd_Log::warning("copy res success... [courseId:{$course['courseId']},res:{$course['coverImg']}]");
            }
        }
    }
}
unset($arrCourse);

Bd_Log::warning("copy course end....");

Bd_Log::warning("send email start....");

sendEmail(json_encode($arrResult));

Bd_Log::warning("send email end....");

function sendEmail($content) {
    $idc = Bd_Conf::getConf('idc/cur');
    if ($idc == 'yun') {
        $subject = '【浣熊英语 - 同步bos资源通知】';
        Hk_Util_Mail::sendMail('zhaojinsong@zuoyebang.com', $subject, $content);
    }
}

/**
 * 重试3次，只有能获取到才返回true
 * @param $objBos
 * @param $src
 * @param $srcBucket
 * @return bool
 */
function copyRes($objBos, $src, $srcBucket) {
    //先判断是否已经拷贝
    $res = $objBos->getObjectMeta($src);
    if (false !== $res) {
        return true;
    }
    //没拷贝过，重新拷贝
    for ($i = 0; $i < 3; $i++) {
        $ret = $objBos->copyObject($src, $src, $srcBucket);
        if (true === $ret) {
            //拷贝成功，getObject判断是否成功
            $res = $objBos->getObjectMeta($src);
            if (false !== $res) {
                return true;
            }
        }
    }
    return false;
}



