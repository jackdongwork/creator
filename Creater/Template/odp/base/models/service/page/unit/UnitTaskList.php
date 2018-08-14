<?php
/**
 * file  : UnitList.php
 * author: chenzhiwen@zuoyebang.com
 * date  : 2018/2/12
 * brief : 单元ps
 */
class Service_Page_Unit_UnitTaskList
{
	private $_objUnit;
	private $_objCourse;
	private $_arrOutput;
        private $_objTask;

        public function __construct() {
		$this->_objUnit   = new Fz_Ds_Unit();
		$this->_objCourse = new Fz_Ds_Course();
                $this->_objTask = new Fz_Ds_Task();
	}

	public function execute($arrInput) {
		//参数校验
		Fz_Util_Log::start('ps_check_param');
		$arrInput = self::checkParam($arrInput);
		Fz_Util_Log::stop('ps_check_param');

		Fz_Util_Log::start('ds_unit_list');

		$pn         = $arrInput['pn'];
		$rn         = $arrInput['rn'];
		$unitId     = $arrInput['unitId'];
		$unitName   = $arrInput['unitName'];
		$operator   = $arrInput['operator'];
		$list 	    = array();
		$conds      = array();

		$this->_arrOutput['pn']    = $pn;
		$this->_arrOutput['rn']    = $rn;
		$this->_arrOutput['list']  = $list;
		$this->_arrOutput['conds'] = $conds;

		//确定查询字段
		$arrFields = array(
			'unitId',
			'unitName',
			'taskList',
			'unitLevel',
			'operator',
		);

		//拼装查询条件  后期确认后修改
		$arrCond = array(
			'deleted' => Fz_Ds_Unit::DELETE_NO,
		);
		if ($unitId !== '' && intval($unitId) >= 0 ) {
			$arrCond['unitId'] = $unitId;
		}
		if (strlen(trim($unitName)) > 0) {
			$unitName = bin2hex("%$unitName%");
			$arrCond[] = " unit_name like unhex('$unitName')";
		}
                 $arrConds['taskId']=$arrInput['tid'];
                 $info= $this->_objTask->getTaskCondInfo($arrConds);
                 if ( $info['unitId']){
                 $arrCond[]='task_list ="" or task_list = "[]" or unit_id in('.  $info['unitId'].")";
                 }else{
                    $arrCond[]='task_list ="" or task_list = "[]"';
                 }
		//总条数
		$total = $this->_objUnit->getCntByCond($arrCond);
		$this->_arrOutput['total'] = ($total)?$total:0;

		//数据列表
		$list = $this->_objUnit->getListByCond($arrCond,$arrFields,$pn*$rn,$rn);
		if ($list !== false) {
			foreach ($list as $key => $item) {
				//单元等级字段
				$list[$key]['unitLevel'] = Fz_Ds_Unit::$UNIT_LEVEL_ARRAY[$item['unitLevel']];
				//任务配置字端
				$list[$key]['taskList']  = empty($item['taskList']) ? '未配置' : '已配置';
				//是否应用在课程中,并且课程被配置为商品
				//1.获取课程中是否有配置为商品的课程
				$arrCourse = $list[$key]['courseIdList'];
				$isProduct = $this->_objCourse->isHaveProductInCourse($arrCourse);
				$list[$key]['isProduct'] = $isProduct;
			}
		}
		$this->_arrOutput['list'] = ($list)?$list:array();

		foreach (Fz_Ds_Unit::$CONDS_ARRAY as $key => $value) {
			$this->_arrOutput['conds'][] = array(
				'value' => $key,
				'label' => $value,
			);
		}

		Hk_Util_Log::stop('ds_unit_list');
		return $this->_arrOutput;
	}

	/**
	 * 参数校验
	 * @param  array $arrInput
	 * @return array
	 */
	private function checkParam($arrInput) {
		if ($arrInput['rn'] == 0){
			$arrInput['rn'] = 30;
		}
		return $arrInput;
	}
}