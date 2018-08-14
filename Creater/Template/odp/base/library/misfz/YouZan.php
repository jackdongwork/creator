<?php

/**
 * Created by PhpStorm.
 * User: zhengzhiqing@zuoyebang.com
 * Date: 2018/2/1
 * Time: 21:10
 * brief: 有赞API
 */
class MisFz_YouZan
{
    /***** 正式 *****/
    const CLIENT_NO     = "40381959";                           //店铺ID
    const CLIENT_ID     = "125c016989d8c07c67";                 //应用ID
    const CLIENT_SECRET = "198e9550a9a3a16149cb0ae710f20a4d";   //密钥
    /***** 正式 *****/

    const API_VERSION   = '3.0.0';                              //要调用的api版本号
    const TOKEN_URL     = "https://open.youzan.com/oauth/token";//post获取token

    private $_objYouZan = null;
    private $_token     = '';

    public function __construct() {
        $idc = Bd_Conf::getConf('idc/cur');
        if ($idc != 'yun') { // 线下测试记得改成测试ID
            throw new MisFz_Exception(MisFz_ExceptionCodes::API_ERROR, 'yun error');
        }
        require_once __DIR__ . '/youzan/YZTokenClient.php';
        $this->_token     = $this->getToken(self::CLIENT_ID, self::CLIENT_SECRET, self::CLIENT_NO);
        $this->_objYouZan = new YZTokenClient($this->_token);
    }

    /**
     * @param $clientId
     * @param $clientSecret
     * @param $clientNo
     * @return string
     * @throws Exception
     */
    public function getToken($clientId, $clientSecret, $clientNo) {
        $token  = '';
        $params = array(
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'grant_type'    => "silent",
            'kdt_id'        => $clientNo,
        );

        $result = MisFz_Comm::CurlPost(self::TOKEN_URL, $params);
        $json   = json_decode($result, true);
        if ($result && $json && isset($json['access_token'])) {
            $token = $json['access_token'];
        }
        if (empty($token)) {
            throw new MisFz_Exception(MisFz_ExceptionCodes::API_ERROR, 'get yz access_token fail');
        }
        return $token;
    }

    // 新增商品
    public function createItem($arrParam) {
        $method = 'youzan.item.create';
        return $this->_objYouZan->post($method, self::API_VERSION, $arrParam);
    }

    // 更新商品
    public function updateItem($arrParam) {
        $method = 'youzan.item.update';
        return $this->_objYouZan->post($method, self::API_VERSION, $arrParam);
    }

    // 更新商品库存
    public function updateItemSku($arrParam) {
        $method = 'youzan.item.quantity.update';
        return $this->_objYouZan->post($method, self::API_VERSION, $arrParam);
    }

    // 删除商品
    public function deleteItem($arrParam) {
        $method = 'youzan.item.delete';
        return $this->_objYouZan->post($method, self::API_VERSION, $arrParam);
    }

    // 上架商品
    public function onListItem($arrParam) {
        $method = 'youzan.item.update.listing';
        return $this->_objYouZan->post($method, self::API_VERSION, $arrParam);
    }

    // 下架商品
    public function unListItem($arrParam) {
        $method = 'youzan.item.update.delisting';
        return $this->_objYouZan->post($method, self::API_VERSION, $arrParam);
    }

    // 商品详情
    public function itemDetail($arrParam) {
        $method = 'youzan.item.get';
        return $this->_objYouZan->post($method, self::API_VERSION, $arrParam);
    }

    // 订单列表
    public function tradeSoldList($arrParam) {
        $method = 'youzan.trades.sold.get';
        return $this->_objYouZan->post($method, self::API_VERSION, $arrParam);
    }
    
    // 订单详情
    public function tradeDetail($arrParam) {
        $method = 'youzan.trade.get';
        return $this->_objYouZan->post($method, self::API_VERSION, $arrParam);
    }

    // 上传
    public function imgUpload($arrFile) {
        $method = 'youzan.materials.storage.platform.img.upload';
        return $this->_objYouZan->post($method, self::API_VERSION, array(), $arrFile);
    }
}