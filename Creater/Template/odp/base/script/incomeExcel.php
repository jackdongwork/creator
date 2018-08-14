<?php
/**
 * @file    IncomeExcel.php
 * @author  wangyang01(wangyang01@zuoyebang.com)
 * @date    2018-03-10
 * @brief    订单统计
 *  *  每天早上八点跑一次
 *   0 8 * * * cd /home/homework/app/misfz/script && /home/homework/php/bin/php incomeExcel.php >/dev/null 2>&1
 */

Bd_Init::init('misfz');
$obj = new IncomeExcel();
$obj->execute();



class IncomeExcel {


    private $_objDsYZOrder;


    public function __construct()
    {
        $this->_objDsYZOrder    = new Fz_Ds_YZOrder();
        $this->_objDaoYZOrder   = new Fz_Dao_YZOrder();
        $this->_objProduct      = new Fz_Ds_Product();
    }
    public function execute() {
        $strDate = date("Y-m-d");
        $endTime = strtotime($strDate);
        $startTime  = $endTime - 86400;
        $sql = "select * from tblYZOrder0 where create_time >=  $startTime and create_time <=$endTime  and source_id  in(1,3)";
        $arrOrderList = $this->_objDaoYZOrder->query($sql);
        $arrIncomeData = array();
        $arrRefundData = array();
        $fileArray = array();
        if (!empty($arrOrderList)) {
            foreach ($arrOrderList as $orderDetail) {
                $data = array();
                $productId              = intval($orderDetail['product_id']);
                $productInfo            = $this->_objProduct->getProductInfo($productId,array('productTitle'));
                if($orderDetail['status'] == 1 && $orderDetail['deleted'] == 0){
                    $data['countTime']      = date('Y-m-d',$startTime); //统计日期
                    $data['orderId']        = $orderDetail['tid'];//订单号
                    $data['payNum']         = ($orderDetail['payment'] /100)."元" ;
                    $data['productName']    = $productInfo['productTitle'];
                    $data['source']         = Fz_Ds_YZOrder::$SOURCE_ARRAY[$orderDetail['source_id']];
                    $data['phone']          = $orderDetail['phone'];
                    $data['orderTime']      = date('Y-m-d H:i:s',$orderDetail['pay_time']);
                    $arrIncomeData[] = $data;
                }  elseif($orderDetail['status'] == 3 ){//退款的
                    $orderDetail['ext_data'] = json_decode($orderDetail['ext_data'],true);
                    $data['countTime']      = date('Y-m-d',$startTime); //统计日期
                    $data['orderId']        = $orderDetail['tid'];//订单号
                    $data['payNum']         = ($orderDetail['payment'] /100)."元";//实收金额
                    $data['refundNum']      = ($orderDetail['ext_data']['refundFee']/100)."元";
                    $data['productName']    = $productInfo['productTitle'];
                    $data['source']         = Fz_Ds_YZOrder::$SOURCE_ARRAY[$orderDetail['source_id']];
                    $data['phone']          = $orderDetail['phone'];
                    $data['orderTime']      = date('Y-m-d H:i:s',$orderDetail['pay_time']);
                    $refundTime     = isset($orderDetail['ext_data']['refundTime']) ? $orderDetail['ext_data']['refundTime'] :$orderDetail['update_time'] ;
                    $data['refundTime']     = date('Y-m-d H:i:s',$refundTime);
                    $arrRefundData[]         = $data;
                }
            }
            $excelRefundTitle = array(
                'countTime'     => '统计日期',
                'orderId'       => '当日退费订单号',
                'payNum'        => '实收金额'  ,
                'refundNum'     => '退费金额',
                'productName'   => '商品名称' ,
                'source'        => '订单来源',
                'phone'         => '订单手机'  ,
                'orderTime'     => '下单时间' ,
                'refundTime'    =>'退费时间',
            );
            $excelIncomeTitle = array(
                'countTime'  => '统计日期'   ,
                'orderId'=> '当日订单号'  ,
                'payNum'=>'订单实收',
                'productName' => '商品名称',
                'source'        => '订单来源',
                'phone'=> '订单手机' ,
                'orderTime'=>'下单时间'  ,
            );

            if(!empty($arrRefundData)){
                array_unshift($arrRefundData, $excelRefundTitle);
              //  Flipped_Comm::genCsv($arrRefundData,'refund');
                $filename = '/tmp/refund'.date('YmdHi', time()).'.xlsx';
                Fz_Service_Excel::outFilePutExcel($arrRefundData,$filename);
                $fileArray[] = $filename;
            }
            if(!empty($arrIncomeData)){
                 array_unshift($arrIncomeData, $excelIncomeTitle);
                $filename = '/tmp/income'.date('YmdHi', time()).'.xlsx';
                Fz_Service_Excel::outFilePutExcel($arrIncomeData,$filename);
             //   Flipped_Comm::genCsv($arrIncomeData,'income');
                $fileArray[] = $filename;
            }
        }
        $this->sendEmail($fileArray);
    }
    private function sendEmail($fileName) {
        $strDate = date("Y-m-d");
        $endTime = strtotime($strDate);
        $startTime  = $endTime - 86400;
        $time = date('Y-m-d',$startTime);
        $subject = $time.'有赞订单情况和退费情况';
        $content ="老师：<br/>
            您好。附件是".$time."的订单情况和退费情况，请查收。谢谢。<br/><br/>
            附注：如果当日没有订单或者退费发生，那么邮件中没有相关附件。谢谢支持。<br/><br/>
            HX项目<br/>
            ".$strDate;
        $ret =   Hk_Util_Mail::sendMail('HXyewuzu@zuoyebang.com', $subject, $content,$fileName);

         if(true === $ret){
             foreach($fileName as $file){
                 unlink($file);
             }
         }

    }
}