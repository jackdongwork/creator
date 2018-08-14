<?php
/**
 * Created by PhpStorm.
 * Time: 2018/7/2 11:11
 * Brief: 清洗学豆记录一次性脚本
 */

Bd_Init::init('misfz');

Bd_Log::notice('开始清洗学生学豆记录');

$obj = new GenBeanLog();

$obj->execute();


Bd_Log::notice('结束清洗学生学豆记录');

class GenBeanLog
{

    private  $_dbCtrl;
	private  $_dbName;
	private  $_objDsProduct;
	private  $_objDsServiceItem;
	private  $_objDsProductService;

    public function __construct() {
        $this->_dbName = 'flipped/zyb_flipped';
        $this->_dbCtrl = Hk_Service_Db::getDB($this->_dbName);
        $this->_objDsProduct = new Fz_Ds_BeanLog();
        $this->_objDsServiceItem = new Fz_Ds_ServiceItem();
        $this->_objDsProductService = new Fz_Ds_ProductServiceItem();
    }
    
    public function execute()
	{
	    $failedList = array();
	    for($i=0;$i<20;$i++) {
	        $sql = "select id,ext_data as extData from tblBeanLog$i where operate_type=1";
	        $result = $this->_dbCtrl->query($sql);
	        if(empty($result)) {
	           continue;
            }
            foreach($result as $k => $row) {
	            $id = intval($row['id']);
	            if(empty($id)) {
	                continue;
                }
                $extData = json_decode($row['extData'], true);
                $productId = !empty($extData['productId']) ? $extData['productId'] : 0;
                $courseId  = !empty($extData['courseId'])  ? $extData['courseId']  : 0;
                $unitId    = !empty($extData['unitId'])    ? $extData['unitId']    : 0;
                $examId    = !empty($extData['examId'])    ? $extData['examId']    : 0;
                $qAttr     = !empty($extData['qAttr'])     ? $extData['qAttr']     : 0;
	            $sql = "update tblBeanLog$i set product_id=$productId,course_id=$courseId,unit_id=$unitId,exam_id=$examId,qAttr=$qAttr where id=$id limit 1";
	            $ret = $this->_dbCtrl->query($sql);
	            if(empty($ret)) {
	                Bd_Log::warning("handler failed,Detail[table $i, $id:]". $row['id']);
	                $failedList[] = "handler failed,Detail[table $i, $id:]". $row['id'];
	                continue;
                }
            }
            sleep(1);
        }
        
        //var_dump($failedList);
        if(!empty($failedList)) {
	        self::sendMail(implode('<br/>', $failedList));
        }
		
	}
    
    private static function sendMail($content) {
        // 邮件报警
        $idc = Bd_Conf::getConf('idc/cur');
        if ($idc == 'yun') {
            $subject = '【翻转 - 清洗学生学豆日志报警 auto】';
            Hk_Util_Mail::sendMail('wangwen@zuoyebang.com', $subject, $content);
        }
    }

}