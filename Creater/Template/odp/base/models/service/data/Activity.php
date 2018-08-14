<?php
/**
 * @file    AddBean.php
 * @author  王洋(wangyang01@zuoyebang.com)
 * @date    2018-03-01
 * @brief   加豆子接口
 */
class Service_Data_Activity {
    protected $_arrOutput;
	private   $_objDsClockDay;
	private   $_objDsActivity;


    public function __construct()
    {
		$this->_objDsClockDay = new Fz_Ds_ClockDay();
		$this->_objDsActivity = new Fz_Ds_Activity();
        $this->_arrOutput     = array();
    }


    public function updateClockActivityTime($activityId = 0) {
		if (empty($activityId)) {
			return array();
		}
		//更新clockday时间
		//1.查找时间
		$arrFields = array (
			'dayId',
			'date',
		);

		$daylist = $this->_objDsClockDay->getClockDaysByActivityId($activityId,$arrFields);
		if (!empty($daylist)) {
			//获取最后一个值
			$cnt = count($daylist);
			$startTime = $daylist[0]['date'];
			$endTime   = $daylist[$cnt-1]['date'];
			//更新活动时间
			$arrFields = array(
				'startTime'    => $startTime,
				'endTime'      => $endTime,
			);
			$this->_arrOutput = $this->_objDsActivity->updateActivityByActivityId($activityId,$arrFields);
		}else{
			//更新活动时间
			$arrFields = array(
				'startTime'    => 0,
				'endTime'      => 0,
			);
			$this->_arrOutput = $this->_objDsActivity->updateActivityByActivityId($activityId,$arrFields);
		}

		return $this->_arrOutput;
    }

}