<?php
/**
 * Created by PhpStorm.
 * Time: 2018/3/20 11:11
 * Brief: 统计校区（班级人数，教师人数，学生人数） 5 分钟一次
 * 分时日月周
 * cd /home/homework/app/misfz/script/once && /home/homework/php/bin/php GenServiceItemInProduct.php >/dev/null 2>&1
 */

Bd_Init::init('misfz');

Bd_Log::notice('开始清洗商品服务项');

$obj = new GenServiceItemInProduct();

$obj->execute();


Bd_Log::notice('结束清洗商品服务项');

class GenServiceItemInProduct
{

    private  $_dbCtrl;
	private  $_dbName;
	private  $_objDsProduct;
	private  $_objDsServiceItem;
	private  $_objDsProductService;

    public function __construct() {
        $this->_dbName = 'flipped/zyb_flipped';
        $this->_dbCtrl = Hk_Service_Db::getDB($this->_dbName);
        $this->_objDsProduct = new Fz_Ds_Product();
        $this->_objDsServiceItem = new Fz_Ds_ServiceItem();
        $this->_objDsProductService = new Fz_Ds_ProductServiceItem();
    }

	//服务项ID
	const S_1 = 1;//教材
	const S_2 = 2;//直播
	const S_3 = 3;//随材A(熊)
	const S_4 = 4;//随材B

    public function execute()
	{
		//服务项关联的商品Id
		$S_P    = array();
		$S_P['1'] = [23,24,25,26,27,28,29,30,31,32,33,34,38,39,40,41,42,43,55,56,57,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,98,99,105,106,109,110,111,112,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,138,139,140,146,154,157,158,159,160,161,174,175,177,178,179];
		$S_P['2'] = [1,2,3,4,5,8,9,10,11,12,13,14,15,16,17,18,19,20,21,23,24,25,26,27,28,29,30,31,32,33,34,36,37,38,39,40,41,42,43,44,45,46,47,48,49,51,52,53,55,56,57,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,82,83,84,86,87,88,89,90,91,92,93,95,96,98,99,105,106,109,110,111,112,113,114,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,138,139,140,142,146,148,151,154,156,157,158,159,160,161,169,174,175,177,178,179,182];
		$S_P['3'] = [23,24,25,26,27,28,29,30,31,32,33,34,38,39,40,41,42,43,55,56,57,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,98,99,129,130,131,132,133,138,139,140];
		$S_P['4'] = [105,106,109,110,111,112,117,118,119,120,121,122,123,124,125,126,127,128,146,154,157,158,159,160,161,174,175,177,178,179];

		$this->_dbCtrl->startTransaction();
		//将商品和service关联插入商品serviceItem关联表
		for ($i=1;$i<5;$i++) {
			$total = count($S_P[$i]);
			$arrParam['serviceId'] = $i;
			for ($j=0;$j<$total;$j++) {
				$pid = $S_P[$i][$j];
				$arrParam['productId'] = $pid;
				$ret = $this->_objDsProductService->addProductServiceItem($arrParam);
				if (false == $ret) {
 					$this->_dbCtrl->rollback();
					throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR,'addPS Error - serviceId:'.$i);
				}
				//商品表中记录服务项
				$pInfo = $this->_objDsProduct->getProductInfo($pid,array('productId','extData'));
				if (false == $pInfo) {
					$this->_dbCtrl->rollback();
					throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR,'getInfo Error - productId:'.$pid);
				}

				if (!isset($pInfo['extData']['serviceItem'])) {
					$pInfo['extData']['serviceItem'] = array(
						'common'   => array(),
						'needSend' => array(),
					);
				}

				//注意区分是否需要发货

				//不需要发货
				if ($i == self::S_2) {
					if (!in_array($i,$pInfo['extData']['serviceItem']['common'])) {
						$pInfo['extData']['serviceItem']['common'][] = $i;
					}
				}
				//需要发货
				if ($i != self::S_2){
					if (!in_array($i,$pInfo['extData']['serviceItem']['needSend'])) {
						$pInfo['extData']['serviceItem']['needSend'][] = $i;
					}
				}

				$arrParams = $pInfo['extData'];
				$ret = $this->_objDsProduct->updateProductExt($pid,$arrParams);
				if (false == $ret) {
					$this->_dbCtrl->rollback();
					throw new MisFz_Exception(MisFz_ExceptionCodes::DB_ERROR,'updateError - productId:'.$pid);
				}
			}
		}


		$this->_dbCtrl->commit();
	}

}