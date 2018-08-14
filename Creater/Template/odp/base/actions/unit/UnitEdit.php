<?php
/**
 * file  : UnitEdit.php
 * author: chenzhiwen@zuoyebang.com
 * date  : 2018/2/12
 * brief : 单元编辑action
 */
class Action_UnitEdit extends MisFz_Action_Base
{
	public function invoke() {
		$arrInput = array(
			'userInfo'    => $this->_userInfo,
			'unitId'      => isset($this->_requestParam['unitId']) ? intval($this->_requestParam['unitId']) : 0,
			'act'         => isset($this->_requestParam['act']) ? strval($this->_requestParam['act']) : '',
			'unitName'    => isset($this->_requestParam['unitName']) ? strval($this->_requestParam['unitName']) : '',
			'unitLevel'   => isset($this->_requestParam['unitLevel']) ? intval($this->_requestParam['unitLevel']) : '',
			'taskList'    => isset($this->_requestParam['taskList']) ? strval($this->_requestParam['taskList']) : '',
			'levelId'     => isset($this->_requestParam['levelId']) ? intval($this->_requestParam['levelId']) : 0,
		);

		Hk_Util_Log::start('ps_unit_edit');
		$objUnitEdit = new Service_Page_Unit_UnitEdit();
		$this->_outPut['data'] = $objUnitEdit->execute($arrInput);
		Hk_Util_Log::stop('ps_course_edit');
	}
}