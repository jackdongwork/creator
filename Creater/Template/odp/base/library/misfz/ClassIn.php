<?php
/**
 * Created by PhpStorm.
 * Time: 2018/7/9 10:46
 * Brief: ClassIn api
 *
 * 临时 - 方案，后期收拢到 DeskFz 项目
 */
class MisFz_ClassIn
{
    /***** 正式 *****/
    const EEO_SID     = '3275350';    // sid
    const EEO_SECRET  = 'OC8fjtzW';   // 密钥
    /***** 正式 *****/

    // eeo 请求 url
    const EEO_REQUEST_URL = 'http://www.eeo.cn/partner/api/course.api.php?action=';
    const EEO_CLOUD_URL   = 'http://www.eeo.cn/partner/api/cloud.api.php?action=';

    private $_timeStamp = '';
    private $_safeKey   = '';

    public function __construct() {
        $this->_timeStamp = time();
        $this->_safeKey   = md5(self::EEO_SECRET.$this->_timeStamp);
    }

    /**
     * @param $action
     * @param $arrParams
     * @param $isCloud
     * @return string
     */
    private function getClassInRequest($action, $arrParams = array(), $isCloud = 0) {
        $idc = Bd_Conf::getConf('idc/cur');
        if ($idc != 'yun') { // 线下测试记得改成测试SID
            Bd_Log::warning("Error:[yun error], detail:[]");
            return false;
        }
        $params = array(
            'SID'         => self::EEO_SID,
            'safeKey'     => $this->_safeKey,
            'timeStamp'   => $this->_timeStamp,
        );
        foreach ($arrParams as $k=>$v) {
            $params[$k] = $v;
        }
        $requestUrl = self::EEO_REQUEST_URL . $action;
        if ($isCloud == 1) {
            $requestUrl = self::EEO_CLOUD_URL . $action;
        }
        $result = MisFz_Comm::CurlPost($requestUrl, $params);
        $json   = json_decode($result, true);
        if (empty($result) || empty($json)) {
            Bd_Log::warning("Error:[request class in api fail], detail:[{$result}]");
            return false;
        }
        if ($json['error_info']['errno'] != 1) {
            Bd_Log::warning("Error:[request class in api fail], detail:[{$result}]");
            return false;
        }
        return $json;
    }

    // 获取云盘文件夹列表
    public function getFolderList() {
        $method = 'getFolderList';
        $ret = $this->getClassInRequest($method, array(), 1);

        return $ret['data'];
    }

    // 注册用户
    public function register($arrParam) {
        $method = 'register';
        if (empty($arrParam['telephone'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['password']) && empty($arrParam['md5pass'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $ret = $this->getClassInRequest($method, $arrParam);
        return $ret['data'];
    }

    // 修改用户昵称
    public function editUserInfo($arrParam) {
        $method = 'editUserInfo';
        if (empty($arrParam['telephone']) || empty($arrParam['nickname'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $this->getClassInRequest($method, $arrParam);
        return true;
    }

    // 修改用户密码
    public function modifyPassword($arrParam) {
        $method = 'modifyPassword';
        if (empty($arrParam['telephone']) || empty($arrParam['oldMd5pass'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['password']) && empty($arrParam['md5pass'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $this->getClassInRequest($method, $arrParam);
        return true;
    }

    // 修改用户密码（不提供原密码）
    public function modifyPasswordByTelephone($arrParam) {
        $method = 'modifyPasswordByTelephone';
        if (empty($arrParam['telephone'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['password']) && empty($arrParam['md5pass'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $this->getClassInRequest($method, $arrParam);
        return true;
    }

    // 添加老师
    public function addTeacher($arrParam) {
        $method = 'addTeacher';
        if (empty($arrParam['teacherAccount']) || empty($arrParam['teacherName'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $ret = $this->getClassInRequest($method, $arrParam);
        return $ret['data'];
    }

    // 编辑老师
    public function editTeacher($arrParam) {
        $method = 'editTeacher';
        if (empty($arrParam['st_id']) || empty($arrParam['teacherName'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $this->getClassInRequest($method, $arrParam);
        return true;
    }

    // 创建课程
    public function addCourse($arrParam) {
        $method = 'addCourse';
        if (empty($arrParam['courseName'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $arrParam['expiryTime'] = strtotime('+1 year');
        $arrParam['folderId'] = 2900685; // 云盘根目录
        $ret = $this->getClassInRequest($method, $arrParam);

        return $ret['data'];
    }

    // 编辑课程
    public function editCourse($arrParam) {
        $method = 'editCourse';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        // 原班主任是否加入教师列表 1加入，2不加入，默认为1
        $arrParam['stamp'] = 2;
        $this->getClassInRequest($method, $arrParam);
        return true;
    }

    // 更换课程老师
    public function modifyCourseTeacher($arrParam) {
        $method = 'modifyCourseTeacher';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['teacherAccount'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $ret = $this->getClassInRequest($method, $arrParam);
        foreach ($ret['data'] as $info) {
            if ($info['errno'] != 1) {
                throw new MisFz_Exception(MisFz_ExceptionCodes::API_ERROR, $info['classId'].$info['error']);
            }
        }
        return $ret['data'];
    }

    // 创建课节(多个)
    public function addCourseClassMultiple($arrParam) {
        $method = 'addCourseClassMultiple';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['classJson'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $ret = $this->getClassInRequest($method, $arrParam);
        foreach ($ret['data'] as $info) {
            if ($info['errno'] != 1) {
                throw new MisFz_Exception(MisFz_ExceptionCodes::API_ERROR, $info['className'].$info['error']);
            }
        }
        return $ret['data'];
    }

    // 修改课节信息
    public function editCourseClass($arrParam) {
        $method = 'editCourseClass';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['classId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $this->getClassInRequest($method, $arrParam);
        return true;
    }

    // 课程下添加学生/旁听（单个）
    public function addCourseStudent($arrParam) {
        $method = 'addCourseStudent';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['studentAccount']) || empty($arrParam['studentName'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }

        // 学生和旁听的识别(1 为学生,2 为旁听)
        $arrParam['identity'] = 1;

        $this->getClassInRequest($method, $arrParam);
        return true;
    }

    // 课程下添加学生/旁听（多个）
    public function addCourseStudentMultiple($arrParam) {
        $method = 'addCourseStudentMultiple';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['studentJson'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }

        // 学生和旁听的识别(1 为学生,2 为旁听)
        $arrParam['identity'] = 1;

        $this->getClassInRequest($method, $arrParam);
        return true;
    }

    // 课程下删除学生/旁听（单个）
    public function delCourseStudent($arrParam) {
        $method = 'delCourseStudent';
        if (empty($arrParam['courseId'])) {
            return false;
        }
        if (empty($arrParam['studentAccount'])) {
            return true;
        }

        // 学生和旁听的识别(1 为学生,2 为旁听)
        $arrParam['identity'] = 1;

        return $this->getClassInRequest($method, $arrParam);
    }

    // 获取课程下学生/旁听
    public function getCourseStudent($arrParam) {
        $method = 'getCourseStudent';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }

        // 学生和旁听的识别(1 为学生,2 为旁听)
        $arrParam['identity'] = 1;

        $ret = $this->getClassInRequest($method, $arrParam);
        return $ret['data'];
    }

    // 获取课节列表
    public function getCourseClass($arrParam) {
        $method = 'getCourseClass';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }

        $ret = $this->getClassInRequest($method, $arrParam);
        return $ret['data'];
    }

    // 获取课节下出勤成员的时间信息
    public function getClassMemberTimeDetails($arrParam) {
        $method = 'getClassMemberTimeDetails';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['classId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $ret = $this->getClassInRequest($method, $arrParam);
        return $ret['data'];
    }

    // 课节查询直播、回放地址
    public function getClassVideo($arrParam) {
        $method = 'getClassVideo';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['classId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $ret = $this->getClassInRequest($method, $arrParam);
        return $ret['data'];
    }

    // 获取课程直播/回放播放器地址
    public function getWebcastUrl($arrParam) {
        $method = 'getWebcastUrl';
        if (empty($arrParam['courseId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        if (empty($arrParam['classId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $ret = $this->getClassInRequest($method, $arrParam);
        return $ret['data'];
    }

    // 获取课节学生对教师的评价
    public function getOneClassTeacherComment($arrParam) {
        $method = 'getOneClassTeacherComment';
        if (empty($arrParam['classId'])) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR, '主参数缺失');
        }
        $ret = $this->getClassInRequest($method, $arrParam);
        return $ret['data'];
    }
}