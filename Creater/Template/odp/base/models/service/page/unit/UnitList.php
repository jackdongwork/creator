<?php
/**
 * file  : UnitList.php
 * author: chenzhiwen@zuoyebang.com
 * date  : 2018/2/12
 * brief : 单元ps
 */
class Service_Page_Unit_UnitList
{
	private $_objUnit;
	private $_objCourse;
	private $_arrOutput;

	public function __construct() {
		$this->_objUnit   = new Fz_Ds_Unit();
		$this->_objCourse = new Fz_Ds_Course();
	}

	public function execute($arrInput) {
		//参数校验
		Hk_Util_Log::start('ps_check_param');
		$arrInput = self::checkParam($arrInput);
		Hk_Util_Log::stop('ps_check_param');

		Hk_Util_Log::start('ds_unit_list');

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
			'courseIdList',
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
		if (strlen(trim($operator)) > 0) {
			$operator = bin2hex("%$operator%");
			$arrCond[] = "operator like unhex('$operator')";
		}
		//总条数
		$total = $this->_objUnit->getCntByCond($arrCond);
		$this->_arrOutput['total'] = $total;

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
				$isProduct = false;
				if (!empty($arrCourse)) {
					$arrConds = 'course_id in (' . implode(',',$arrCourse) . ')';
					$arrFields = array(
						'is_product',
					);
					$offset = 0;
					$limit  = $this->_objCourse->getCntByCond($arrConds);
					$courseInfoList = $this->_objCourse->getListByCond($arrConds, $arrFields, $offset, $limit);
					$temp = false;
					foreach ($courseInfoList as $item) {
						if ($item['is_product'] == Fz_Ds_Course::COURSE_ONLINE_PRODUCT) {
							$temp = true;
						}
					}
					$isProduct = $temp;
					$list[$key]['isProduct'] = $isProduct;
				}else{
					$list[$key]['isProduct'] = $isProduct;
				}
			}
		}
		$this->_arrOutput['list'] = $list;

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
	 * @param $arrInput
	 * @return mixed
	 * @throws MisFz_Exception
	 */
	private function checkParam($arrInput) {
		if (empty($arrInput['userInfo']['uid'])) {
			throw new MisFz_Exception(MisFz_ExceptionCodes::USER_NOT_LOGIN);
		}
		if ($arrInput['rn'] == 0){
			$arrInput['rn'] = 30;
		}
		return $arrInput;
	}
}