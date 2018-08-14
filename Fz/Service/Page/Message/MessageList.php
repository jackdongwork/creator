<?php

/**
 * file  : MessageList.php
 * author: chenzhiwen
 * date  : 2018/08/13
 * brief :
 */
class Service_Page_Message_MessageList  {
	public function __construct() {

	}

	public function execute($arrInput) {
		//参数校验
		Hk_Util_Log::start('ps_check_param');
		$arrInput = self::checkParam($arrInput);
		Hk_Util_Log::stop('ps_check_param');

		Hk_Util_Log::start('{{LOG}}');
        //逻辑代码
		Hk_Util_Log::stop('{{LOG}}');

	}

	private static function checkParam($arrInput) {
		//参数校验逻辑
		return $arrInput;
	}

}
