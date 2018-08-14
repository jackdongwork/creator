<?php
/**
 * file  : UnitList.php
 * author: chenzhiwen@zuoyebang.com
 * date  : 2018/2/12
 * brief : 单元列表action
 */
class Action_UnitTaskList extends MisFz_Action_Base
{
	public function invoke()
	{
		$arrInput = array(
			'pn' 	   => isset($this->_requestParam['pn']) ? intval($this->_requestParam['pn']) : 0,
			'rn' 	   => isset($this->_requestParam['rn']) ? intval($this->_requestParam['rn']) : 0,
			'unitId'   => isset($this->_requestParam['unitId']) ? $this->_requestParam['unitId'] : -1,
			'unitName' => isset($this->_requestParam['unitName']) ? strval($this->_requestParam['unitName']) : '',
			'operator' => isset($this->_requestParam['operator']) ? strval($this->_requestParam['operator']) : '',
                         'tid'   => isset($this->_requestParam['tid']) ? $this->_requestParam['tid'] :0,
		);

		Hk_Util_Log::start('ps_unit_list');
		$objUnitList = new Service_Page_Unit_UnitTaskList();
		$this->_outPut['data'] = $objUnitList->execute($arrInput);
		Hk_Util_Log::stop('ps_unit_list');

	}
}