<?php
/**
 * @file    CopyBosNew.php
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
    'exam'    => array(),
    'course' => array(),
    'petVoice' => array(),
    'fail' => array(),
);

$startQid = 0;
$endQid = 0;

Bd_Log::warning("copy question start...");

for ($i = 0; $i < 1400; $i++) {
    $startQid = $i * 10 + 1;
    $endQid   = $i * 10 + 10;
    $sql = "select qid,ext_data as extData from tblQuestion where qid >= $startQid and qid <= $endQid";
    $arrQuestion = $objDsAccount->query($sql);
    if (false === $arrQuestion) {
        $arrResult['exam'][] = "query tblQuestion error, sql:$sql";
        Bd_Log::warning("query tblQuestion error... [sql:$sql]");
        continue;
    }
    if (empty($arrQuestion)) {
        Bd_Log::warning("query tblQuestion empty... [sql:$sql]");
        continue;
    }
    foreach ($arrQuestion as $question) {
        $qid = intval($question['qid']);;
        $extData = json_decode($question['extData'], true);
        $extData = $extData[0];

        Bd_Log::warning("question $qid start.....");

        //宠物语音
        $petVoiceFile = $extData['petVoiceFile'];
        if (!empty($petVoiceFile)) {
            $ret = copyRes($objBos, $petVoiceFile, $srcBucket);
            if (false === $ret) {
                Bd_Log::warning("copy res error... [qid:$qid,res:$petVoiceFile]");
                $arrResult['fail']['res'][] = $qid. ',' .$petVoiceFile;
                continue;
            } else {
                Bd_Log::warning("copy res success... [qid:$qid,res:$petVoiceFile]");
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
                    if (empty($env)) {
                        continue;
                    }
                    $ret = copyRes($objBos, $env, $srcBucket);
                    if (false === $ret) {
                        Bd_Log::warning("copy res error... [qid:$qid,res:$env]");
                        $arrResult['fail']['res'][] = $qid. ',' .$env;
                        continue;
                    }else {
                        Bd_Log::warning("copy res success... [qid:$qid,res:$env]");
                    }
                }
            }
        }

        //draftDesc
        $draftDescList = $extData['draftDesc'];
        if (!empty($draftDescList) && is_array($draftDescList)) {
            foreach ($draftDescList as $draftDesc) {
                if (!empty($draftDesc['draftDescVoice'])) {
                    $ret = copyRes($objBos, $draftDesc['draftDescVoice'], $srcBucket);
                    if (false === $ret) {
                        Bd_Log::warning("copy res error... [qid:$qid,res:{$draftDesc['draftDescVoice']}]");
                        $arrResult['fail']['res'][] = $qid. ',' .$draftDesc['draftDescVoice'];
                        continue;
                    } else {
                        Bd_Log::warning("copy res success... [qid:$qid,res:{$draftDesc['draftDescVoice']}]");
                    }
                }
            }
        }
        //interfereAttr
        $interfereAttrList = $extData['interfereAttr'];
        if (!empty($interfereAttrList) && is_array($interfereAttrList)) {
            foreach ($interfereAttrList as $interfereAttr) {
                if (!empty($interfereAttr['interfereAttrVoice'])) {
                    $ret = copyRes($objBos, $interfereAttr['interfereAttrVoice'], $srcBucket);
                    if (false === $ret) {
                        Bd_Log::warning("copy res error... [qid:$qid,res:{$interfereAttr['interfereAttrVoice']}]");
                        $arrResult['fail']['res'][] = $qid. ',' .$interfereAttr['interfereAttrVoice'];
                        continue;
                    } else {
                        Bd_Log::warning("copy res success... [qid:$qid,res:{$interfereAttr['interfereAttrVoice']}]");
                    }
                }
            }
        }

        //options
        $options = $extData['options'];
        if (!empty($options) && is_array($options)) {
            foreach ($options as $option) {
                $optionImg = $option['optionImg'];
                $ret = copyRes($objBos, $optionImg, $srcBucket);
                if (false === $ret) {
                    Bd_Log::warning("copy res error... [qid:$qid,res:$optionImg]");
                    $arrResult['fail']['res'][] = $qid. ',' .$optionImg;
                } else {
                    Bd_Log::warning("copy res success... [qid:$qid,res:$optionImg]");
                }
                $optionAudio = $option['optionAudio'];
                $ret = copyRes($objBos, $optionAudio, $srcBucket);
                if (false === $ret) {
                    Bd_Log::warning("copy res error... [qid:$qid,res:$optionAudio]");
                    $arrResult['fail']['res'][] = $qid. ',' .$optionAudio;
                } else {
                    Bd_Log::warning("copy res success... [qid:$qid,res:$optionAudio]");
                }
            }
        }

        //optionAudio
        $optionAudio = $extData['optionAudio'];
        if (!empty($optionAudio) && is_array($optionAudio)) {
            foreach ($optionAudio as $pos => $img) {
                $ret = copyRes($objBos, $img[$pos + 1], $srcBucket);
                if (false === $ret) {
                    Bd_Log::warning("copy res error... [qid:$qid,res:{$img[$pos + 1]}]");
                    $arrResult['fail']['res'][] = $qid. ',' .$img[$pos + 1];
                    continue;
                } else {
                    Bd_Log::warning("copy res success... [qid:$qid,res:{$img[$pos + 1]}]");
                }
            }
        }

        //optionImg
        $optionImg = $extData['optionImg'];
        if (!empty($optionImg) && is_array($optionImg)) {
            foreach ($optionImg as $pos => $img) {
                $ret = copyRes($objBos, $img[$pos + 1], $srcBucket);
                if (false === $ret) {
                    Bd_Log::warning("copy res error... [qid:$qid,res:{$img[$pos + 1]}]");
                    $arrResult['fail']['res'][] = $qid. ',' .$img[$pos + 1];
                    continue;
                } else {
                    Bd_Log::warning("copy res success... [qid:$qid,res:{$img[$pos + 1]}]");
                }
            }
        }
        Bd_Log::warning("question $qid end.....");
        //end
    }

    Bd_Log::warning("question [$startQid,$endQid] end and sleep 1 second....");
    sleep(2);
}

Bd_Log::warning("copy question end...");
unset($arrQuestion);



//所有course的封面
$sql = "select course_id as courseId,cover_img as coverImg from tblCourse";
$arrCourse = $objDsAccount->query($sql);
if (false === $arrCourse) {
    $arrResult['fail'][] = '获取tblCourse数据失败，sql:$sql';
    Bd_Log::warning("query tblCourse error... [sql:$sql]");
}

Bd_Log::warning("copy course start....");

if (!empty($arrCourse)) {
    foreach ($arrCourse as $course) {
        if (!empty($course['coverImg'])) {
            //$ret = $objBos->copyObject($course['coverImg'], $course['coverImg'], 'zyb-charge');
            $ret = copyRes($objBos, $course['coverImg'], $srcBucket);
            if (false === $ret) {
                Bd_Log::warning("copy res error... [courseId:{$course['courseId']},res:{$course['coverImg']}]");
                $arrResult['course'][] = $course['courseId'] . ',' .$course['coverImg'];
                continue;
            } else {
                Bd_Log::warning("copy res success... [courseId:{$course['courseId']},res:{$course['coverImg']}]");
            }
        }
    }
}
unset($arrCourse);

Bd_Log::warning("copy course end....");


sleep(2);


//萌宠语音
$sql = "select pet_voice_id,pet_voice_file from tblPetVoice";
$arrPetVoice = $objDsAccount->query($sql);
if (false === $arrPetVoice) {
    $arrResult['fail'][] = '获取tblPetVoice数据失败，sql:$sql';
    Bd_Log::warning("query tblPetVoice error... [sql:$sql]");
}
Bd_Log::warning("copy petVoice start....");
if (!empty($arrPetVoice)) {
    foreach ($arrPetVoice as $petVoice) {
        $petVoiceId = intval($petVoice['pet_voice_id']);
        $petVoiceFile = $petVoice['pet_voice_file'];
        $ret = copyRes($objBos, $petVoiceFile, $srcBucket);
        if (false === $ret) {
            Bd_Log::warning("copy res error... [petVoiceId:$petVoiceId,res:$petVoiceFile]");
            $arrResult['petVoice'][] = $petVoiceId . ',' .$petVoiceFile;
            continue;
        } else {
            Bd_Log::warning("copy res success... [petVoiceId:$petVoiceId,res:$petVoiceFile]");
        }
    }
}
Bd_Log::warning("copy petVoice end....");





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
    $ret = $objBos->getObjectMeta($src);
    if (false !== $ret) {
        Bd_Log::addNotice("copyPos", 1);
        return true;
    }
    //没拷贝过，重新拷贝
    $ret = $objBos->copyObject($src, $src, $srcBucket);
    if (false !== $ret) {
        Bd_Log::addNotice("copyPos", 2);
        return true;
    }
    Bd_Log::addNotice("copyPos", 0);
    return false;
//    for ($i = 0; $i < 3; $i++) {
//        $ret = $objBos->copyObject($src, $src, $srcBucket);
//        if (true === $ret) {
//            //拷贝成功，getObject判断是否成功
//            $ret = $objBos->getObjectMeta($src);
//            if (false !== $ret) {
//                return true;
//            }
//        }
//    }
//    return false;
}
