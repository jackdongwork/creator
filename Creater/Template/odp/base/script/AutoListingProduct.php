<?php
/**
 * Created by PhpStorm.
 * Time: 2018/2/10 21:54
 * Brief: 自动上下架商品
 * 每10分钟执行
 * * /10 * * * * cd /home/homework/app/misfz/script && /home/homework/php/bin/php AutoListingProduct.php >/dev/null 2>&1
 */

Bd_Init::init('misfz');

$obj = new AutoListingProduct();
$obj->execute();

class AutoListingProduct {

    private $_dbName;
    private $_dbCtrl;

    public function __construct() {

        $this->_dbName = 'flipped/zyb_flipped';
        $this->_dbCtrl = Hk_Service_Db::getDB($this->_dbName);
    }

    public function execute() {

        $objYZ = new MisFz_YouZan();

        $time = time();

        // 自动上架
        $sql = "select product_id,nature,ext_data from tblProduct where online_start <= $time and online_stop > $time and online_stop > 0 and status = 0 and deleted = 0";
        $list = $this->_dbCtrl->query($sql);
        foreach ($list as $k=>$v) {
            $productId = $v['product_id'];
            $nature = $v['nature'];
            $ext = @json_decode($v['ext_data'], true);
            $itemId = $ext['itemId'];
            if (in_array($nature, array(Fz_Ds_Product::NATURE_LONG, Fz_Ds_Product::NATURE_TRAINING, Fz_Ds_Product::NATURE_EXPERIENCE)) && !empty($itemId)) {
                $arrParams = array(
                    'item_id'  => $itemId,
                );
                $ret = $objYZ->onListItem($arrParams);
                if (isset($ret['error_response']) || $ret['response']['is_success'] == false) {
                    continue;
                }
            }
            $sql = "update tblProduct set status = 1 where product_id = '{$productId}'";
            $this->_dbCtrl->query($sql);
        }

        // 自动下架
        $sql = "select product_id,nature,ext_data from tblProduct where online_stop < $time and status = 1 and deleted = 0";
        $list = $this->_dbCtrl->query($sql);
        foreach ($list as $k=>$v) {
            $productId = $v['product_id'];
            $nature = $v['nature'];
            $ext = @json_decode($v['ext_data'], true);
            $itemId = $ext['itemId'];
            if (in_array($nature, array(Fz_Ds_Product::NATURE_LONG, Fz_Ds_Product::NATURE_TRAINING, Fz_Ds_Product::NATURE_EXPERIENCE)) && !empty($itemId)) {
                $arrParams = array(
                    'item_id'  => $itemId,
                );
                $ret = $objYZ->unListItem($arrParams);
                if (isset($ret['error_response']) || $ret['response']['is_success'] == false) {
                    continue;
                }
            }
            $sql = "update tblProduct set status = 2 where product_id = '{$productId}'";
            $this->_dbCtrl->query($sql);

        }

    }
}