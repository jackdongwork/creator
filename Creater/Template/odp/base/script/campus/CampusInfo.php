<?php
/**
 * Created by PhpStorm.
 * Time: 2018/3/20 11:11
 * Brief: 统计校区（班级人数，教师人数，学生人数） 5 分钟一次
 * 分时日月周
 * * /5 * * * * cd /home/homework/app/misfz/script && /home/homework/php/bin/php CampusInfo.php >/dev/null 2>&1
 */


Bd_Init::init('misfz');


Bd_Log::notice('开始校区信息统计');
$obj = new CampusInfoCount();
$obj->execute();
Bd_Log::notice('校区信息统计结束');

class CampusInfoCount {

    protected $intPageRow       =   500;
    protected $intCampusTotal   =   0;
    protected $intCurrentPage   =   0;
    protected $intTotalPage     =   0;
    protected $data             =   array();
    protected $fileLock         =   './campusInfo.lock';
    protected $db;
    protected $_dbName;
    protected $_objDsClassStudent;
    protected $_objDsCampus;


    public function __construct() {
        $this->_dbName = 'flipped/zyb_flipped';
        $this->db = Hk_Service_Db::getDB($this->_dbName);
        $this->_objDsClassStudent = new Fz_Ds_ClassStudent();
        $this->_objDsCampus       = new Fz_Ds_Campus();
    }

    public function execute()
    {
//        if( $this->isLock() ) {
//            echo 'task already run' . PHP_EOL;
//            return false;
//        }
//        $this->lock();

        $sql = "SELECT COUNT(*) as con FROM tblCampus";
        $queryResult = $this->db->query($sql);
        $this->intCampusTotal = intval($queryResult[0]['con']);
        $this->intTotalPage   =   ceil($this->intCampusTotal/$this->intPageRow);

        if( $this->intCampusTotal < 1 ) {
            return false;
        }

        while($list = $this->nextPage()) {
            $arrCountResult =   $this->count($list);
            $teacherCount   =   $arrCountResult['teacher'];
            $classCount     =   $arrCountResult['class'];
            $studentCount   =   $arrCountResult['student'];

            foreach( $list as &$item ) {
                $index      = $item['campus_id'];
                $teacherNum = isset($teacherCount[$index]) ? $teacherCount[$index] : 0;
                $classNum   = isset($classCount[$index]) ? $classCount[$index] : 0;
                $studentNum = isset($studentCount[$index]) ? $studentCount[$index] : 0;
                $data       = array();
                if( $item['teacher_number'] != $teacherNum ) {
                    $data['teacherNumber']  =   $teacherNum;
                }
                if( $item['class_number'] != $classNum ) {
                    $data['classNumber']  =   $classNum;
                }
                if( $item['student_number'] != $studentNum ) {
                    $data['studentNumber']  =   $studentNum;
                }
                if( !empty($data) ) {
                    $condition = array(
                        'campusId'  =>  $item['campus_id'],
                    );
                    $saveResult = $this->_objDsCampus->updateCampus($data, $condition);
                }
            }
        }
//        $this->unlock();
        return true;
    }

    /**
     * @return bool
     */
    public function lock()
    {
        $handle = fopen($this->fileLock, 'w');
        if( false === $handle ) {
            return false;
        }
        return fclose($handle);
    }

    /**
     * 解锁
     * @return void
     */
    public function unlock()
    {
        if( file_exists($this->fileLock) ) {
            @unlink($this->fileLock);
        }
    }

    /**
     * 是否被锁定
     * @return bool
     */
    public function isLock()
    {
        return file_exists($this->fileLock);
    }

    /**
     * 校区列表下一页
     * @return array|bool
     */
    public function nextPage()
    {
        if( $this->intCurrentPage > $this->intTotalPage ) {
            return false;
        }
        $offset = $this->intCurrentPage * $this->intPageRow;
        $limit  = $this->intPageRow;
        $sql = "SELECT campus_id,teacher_number, class_number,student_number  FROM  tblCampus LIMIT {$offset},{$limit}";
        $arrCampusList = $this->db->query($sql);
        $arrCampusList = !empty($arrCampusList) ? $arrCampusList : array();
        if( empty($arrCampusList) ) {
            return false;
        }
        $this->intCurrentPage++;
        return $arrCampusList;
    }

    /**
     * 校区下数量统计（班级数，教师数，学生数）
     * @param $campusList
     * @return array
     */
    public function count($campusList)
    {
        $arrCampusIds   =   MisFz_ArrayUtil::column($campusList, 'campus_id');
        $strCampusIds = MisFz_ArrayUtil::implode(',', $arrCampusIds);
        Bd_Log::notice("统计校区[{$strCampusIds}]");

        //统计老师人数
        $teacherStatus  =   Fz_Ds_Teacher::STATUS_OK;
        $teacherDel     =   Fz_Ds_Teacher::DELETED_NO;
        $teacherSql    = "SELECT campus_id,count(*) AS con FROM tblTeacher 
                            WHERE campus_id IN({$strCampusIds}) 
                            AND deleted=$teacherDel 
                            AND status={$teacherStatus}
                            GROUP BY campus_id";
        $arrTeachCount = $this->db->query($teacherSql);
        $arrTeachCount = MisFz_ArrayUtil::columnToIndex('campus_id', $arrTeachCount);
        array_walk($arrTeachCount, function(&$teacher) {
            $teacher = intval($teacher['con']);
        });

        //统计班级数
        $classDel       =   Fz_Ds_ClassInfo::DELETED_FALSE;
        $classSql       = "SELECT campus_id,class_id FROM tblClassInfo WHERE campus_id IN({$strCampusIds}) AND deleted={$classDel}";
        $classList      = $this->db->query($classSql);
        $classCampusGroup= MisFz_ArrayUtil::columnGroup($classList, 'campus_id');
        $classCount     =   array();
        foreach( $classCampusGroup as $campusId=>$arrClassList ) {
            $classCount[$campusId]  =   count($arrClassList);
        }
        unset($campusId, $arrClassList);

        //统计学生人数
        $studentCount = array();
        foreach( $classCampusGroup as $campusId=>$classList ) {
            $campusStudentNumber  =   0;
            foreach($classList as $class ) {
                $classId = (int)$class['class_id'];
                $conds  =   array(
                    'deleted'   =>  Fz_Ds_ClassStudent::DELETED_FALSE,
                    'classId'  =>  $classId,
                );
                $classStudentNumber = $this->_objDsClassStudent->getCntByCond($classId, $conds);
                $campusStudentNumber += $classStudentNumber;
            }

            $studentCount[$campusId] = $campusStudentNumber;
        }

        //拼装统计数据
        return array(
            'teacher'   =>  $arrTeachCount,
            'class'     =>  $classCount,
            'student'   =>  $studentCount,
        );

    }

}