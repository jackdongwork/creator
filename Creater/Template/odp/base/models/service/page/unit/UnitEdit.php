<?php
/**
 * file  : UnitEdit.php
 * author: chenzhiwen@zuoyebang.com
 * date  : 2018/2/12
 * brief : 单元编辑ps
 */
class Service_Page_Unit_UnitEdit extends MisFz_TransBase
{
	private $_objDsUnit;
	private $_objDsTask;
	private $_objDsCourse;

	public function __construct() {
		parent::__construct();
		$this->_objDsUnit   = new Fz_Ds_Unit();
		$this->_objDsTask   = new Fz_Ds_Task();
		$this->_objDsCourse = new Fz_Ds_Course();
	}

	public function execute($arrInput) {
		//参数校验
		Hk_Util_Log::start('ps_ckeck_param');
		$arrInput = self::checkParam($arrInput);
		Hk_Util_Log::stop('ps_check_param');

		$unitId    = $arrInput['unitId'];
		$userInfo  = $arrInput['userInfo'];
		$act       = $arrInput['act'];
		$unitName  = $arrInput['unitName'];
		$unitLevel = $arrInput['unitLevel'];
		$taskId    = $arrInput['taskList'];
		$levelId   = $arrInput['levelId'];
		//创建单元前显示单元列表
		if ($act == 'beforeAdd') {
			$arrLevel = array();
			foreach (Fz_Ds_Unit::$UNIT_LEVEL_ARRAY as $k => $v) {
				$arrLevel[] = array(
					'level'     => $k,
					'levelName' => $v,
				);
			}
			$this->_arrOutPut = $arrLevel;
		}

		//创建单元
		if ($act == 'add') {
			$arrFields = array(
				'unitName'    => $unitName,
				'unitLevel'   => $unitLevel,
				'operatorUid' => $userInfo['uid'],
				'operator'    => $userInfo['nickName'],
			);

			$ret = $this->_objDsUnit->createUnit($arrFields);
			if (false == $ret) {
				throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '创建单元失败');
			}
			$this->_arrOutPut = array(
			    'unitId'    =>  $ret,
            );
		}

		//查看单元信息
		if ($act == 'view') {
			$arrFields = array(
				'unitName',
				'taskList',
				'unitLevel',
			);
			$arrConds = array(
				'unitId' => $unitId,
			);
			//根据单元id获取单元信息
			$unitInfo = $this->_objDsUnit->getUnitCondInfo($arrConds,$arrFields);

			$arrTaskConds = array(
				'taskType' => Fz_Ds_Task::TYPE_TASK,
				'deleted'  => Fz_Ds_Task::DELETE_NO,
				'unitId'   => Fz_Ds_Task::TASK_DEFAULT_UNIT,
			);
			$arrTaskFields = array(
				'taskId',
				'taskName',
			);
			$taskList     = $this->_objDsTask->getTaskCondList($arrTaskConds, $arrTaskFields);
			$arrTaskConds = array(
				'taskType'=> Fz_Ds_Task::TYPE_TASK,
				'deleted' => Fz_Ds_Task::DELETE_NO,
				'unitId'  => $unitId,
			);
			$taskSelfList = $this->_objDsTask->getTaskCondList($arrTaskConds, $arrTaskFields);
			$taskList = array_merge($taskSelfList,$taskList);
			$arrLevel = array();
			foreach (Fz_Ds_Unit::$UNIT_LEVEL_ARRAY as $k => $v) {
				$arrLevel[] = array(
					'level'     => $k,
					'levelName' => $v,
				);
			}

			$this->_arrOutPut['unitInfo'] = $unitInfo;
			$this->_arrOutPut['taskList'] = $taskList;
			$this->_arrOutPut['arrLevel'] = $arrLevel;
		}

		//更新单元 为单元添加任务
		if($act == 'update') {
			//绑定课程信息
			$ret = $this->bindTask($unitId,$taskId,$unitName,$levelId);
			if (false == $ret) {
				throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '更新单元失败');
			}else{
				$ret = true;
			}
			$this->_arrOutPut = $ret;
		}

		//删除单元
		if($act == 'del') {
			$this->startTrans();
			//1.任务解绑单元 (旧任务解绑当前单元)
			//获取当前单元的旧任务
			$arrUnitConds = array(
				'unitId' => $unitId,
			);
			$arrUnitFields = array(
				'taskList',
			);
			$unitInfo = $this->_objDsUnit->getUnitCondInfo($arrUnitConds, $arrUnitFields);
			//重置旧任务的单元unit_id为0
			if (!empty($unitInfo['taskList'])) {
				foreach ($unitInfo['taskList'] as $vTaskId) {
					$arrTaskConds['taskId'] = $vTaskId;
					$arrParams = array(
						'unitId' => Fz_Ds_Task::TASK_DEFAULT_UNIT,
					);
					$taskCntRet = $this->_objDsTask->updateTask($arrTaskConds, $arrParams);
					if (false == $taskCntRet || 0 == $taskCntRet) {
						$this->rollback();
						throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '原有任务解绑单元失败');
					}
				}
			}
			//2.单元解绑课程
			//将课程中关联单元的courseIdList中该课程去除
			//1.获取该单元关联的课程
			$arrUnitConds = array(
				'unitId' => $unitId,
			);
			$arrUnitFields = array(
				'courseIdList',
			);
			$unitInfo  = $this->_objDsUnit->getUnitCondInfo($arrUnitConds, $arrUnitFields);
			//2.如果单元的课程列表不为空,进入if,首先进行课程和单元的字段更新
			if (!empty($unitInfo['courseIdList'])) {
				$delCourse = $unitInfo['courseIdList'];
				//查询课程关联的单元,更新课程的单元列表,删除该单元
				foreach ($delCourse as $vCourseId) {
					//查询出课程的单元列表
					$arrGetCourseFields = array(
						'unitList',
					);
					//获取单元所处的课程列表
					$courseInfo = $this->_objDsCourse->getCourseByCourseId($vCourseId,$arrGetCourseFields);

					if (empty($courseInfo)) {
						$this->rollback();
						throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '获取课程信息失败');
					}
					//删除指定值的数组
					$unitList = $courseInfo['unitList'];

					if (!in_array($unitId,$unitList)) {
						$this->rollback();
						throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '要删除的单元id不存在');
					}
					foreach($unitList as $k=>$v){
						if($v == $unitId){
							unset($unitList[$k]);
						}
					}
					//更新课程的单元列表
					$arrUpCourseConds = array(
						'courseId' => $vCourseId,
					);
					$arrUpCourseParams = array(
						'unitList' => json_encode($unitList),
					);
					$upCourseRet = $this->_objDsCourse->updateCourse($arrUpCourseParams,$arrUpCourseConds);
					if (false == $upCourseRet) {
						$this->rollback();
						throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '更新课程的单元列表失败');
					}
				}
				//置空单元的课程列表
				$arrFields = array(
					'courseIdList'    => NULL,
				);
				$arrConds = array(
					'unitId' => $unitId,
				);
				$updateRet = $this->_objDsUnit->updateUnit($arrConds,$arrFields);
				if (false == $updateRet) {
					$this->rollback();
					throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '置空单元的课程列表失败');
				}
			}
            //单元deleted
			$arrParams = array(
				'deleted'      => Fz_Ds_Unit::DELETE_YES,
			);
			$arrConds = array(
				'unitId' => $unitId,
			);
			$ret = $this->_objDsUnit->updateUnit($arrConds,$arrParams);

			if (false == $ret) {
				$this->rollback();
				throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '删除单元失败');
			}else{
				$ret = true;
			}

			$this->commit();
			$this->_arrOutPut = $ret;
		}

		return $this->_arrOutPut;
	}

	/**
	 * 参数校验
	 * @param $arrInput
	 * @return mixed
	 * @throws MisFz_Exception
	 */
	private static function checkParam($arrInput) {
		if (empty($arrInput['userInfo']['uid'])) {
			throw new MisFz_Exception(MisFz_ExceptionCodes::USER_NOT_LOGIN);
		}
		if (!in_array($arrInput['act'],Fz_Ds_Unit::$ACT)) {
			throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR);
		}
		if ($arrInput['act'] == 'update' || $arrInput['act'] == 'del' || $arrInput['act'] == 'view') {
			if (empty($arrInput['unitId'])) {
				throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR);
			}
		}
		if ($arrInput['act'] == 'add') {
			if (empty($arrInput['unitName']) || empty($arrInput['unitLevel'])){
				throw new MisFz_Exception(MisFz_ExceptionCodes::PARAM_ERROR);
			}
		}
		return $arrInput;
	}


	/**
	 * 单元绑定任务
	 * ps:需要自己开启事务
	 * 例: startTrans();//开启
	 * 	   bingTask();
	 * 	   commit();	//提交
	 * @param $unitId
	 * @param $taskId
	 * @param $unitName
	 * @param $levelId
	 * @return mixed
	 * @throws MisFz_Exception
	 */
	private function bindTask($unitId,$taskId,$unitName = '',$levelId = 0) {
		$this->startTrans();
		//1.任务解绑单元 (旧任务解绑当前单元)
		//获取当前单元的旧任务
		$arrUnitConds = array(
			'unitId' => $unitId,
		);
		$arrUnitFields = array(
			'taskList',
		);
		$unitInfo = $this->_objDsUnit->getUnitCondInfo($arrUnitConds, $arrUnitFields);
		//重置旧任务的单元unit_id为0
		if (!empty($unitInfo['taskList'])) {
			foreach ($unitInfo['taskList'] as $vTaskId) {
				$arrTaskConds['taskId'] = $vTaskId;
				$arrParams = array(
					'unitId' => Fz_Ds_Task::TASK_DEFAULT_UNIT,
				);
				$taskCntRet = $this->_objDsTask->updateTask($arrTaskConds, $arrParams);
				if (false == $taskCntRet || 0 == $taskCntRet) {
					$this->rollback();
					throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '原有任务解绑单元失败');
				}
			}
		}
		//2.新任务绑定当前单元
		$arrTaskConds = array(
			'taskId' => $taskId
		);
		$arrParams = array(
			'unitId' => $unitId,
		);
		$cntRet = $this->_objDsTask->updateTask($arrTaskConds, $arrParams);
		if (false === $cntRet) {
			$this->rollback();
			throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '任务绑定单元失败');
		}

		//3.当前单元绑定新任务(必须首先单元绑定任务,然后任务绑定单元,防止单元绑定任务后原有任务找不到)
		//修改单元信息taskList examList
		//根据任务列表获取ExamList
		$arrTaskConds = array(
			'taskId' => $taskId,
		);
		$arrTaskFields = array(
			'examList',
		);
		$taskInfo = $this->_objDsTask->getTaskCondInfo($arrTaskConds, $arrTaskFields);
		$arrTaskList[] = $taskId;
		$arrConds = array(
			'unitId' => $unitId,
		);
		$arrFields = array(
			'taskList'    => json_encode($arrTaskList),
			'examList'    => json_encode($taskInfo['examList']),
		);
		if (!empty($unitName) && $levelId > 0) {
			$arrFields['unitName']  = $unitName;
			$arrFields['unitLevel'] = $levelId;
		}
		$ret = $this->_objDsUnit->updateUnit($arrConds,$arrFields);

		if (false == $ret) {
			$this->rollback();
			throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR, '单元配置学习任务失败');
		}
		$this->commit();
		return $ret;
	}
}