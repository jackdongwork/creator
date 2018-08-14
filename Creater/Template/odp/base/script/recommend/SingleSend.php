<?php
/**
 * Created by PhpStorm.
 * Time: 2018/3/20 11:11
 * Brief: 推荐位发送51000信令定时脚本 1分钟一次
 * 分时日月周
 * * /1 * * * * cd /home/homework/app/misfz/script && /home/homework/php/bin/php recommend/SingleSend.php >/dev/null 2>&1
 */


Bd_Init::init('misfz');



$obj = new SingleSend();
$obj->execute();


class SingleSend {
    protected $data             =   array();
    protected $fileLock         =   './singleLock.lock';
    protected $db;
    protected $_dbName;
    protected $_objDsRecommend;


    public function __construct() {
        $this->_dbName = 'flipped/zyb_flipped';
        $this->db = Hk_Service_Db::getDB($this->_dbName);
        $this->_objDsRecommend = new Fz_Ds_Recommend();
    }

    public function execute()
    {
//        if( $this->isLock() ) {
//            echo 'task already run' . PHP_EOL;
//            return false;
//        }
//        $this->lock();
        
        $arrCond = array(
            'recommendStatus'   =>  Fz_Ds_Recommend::RECOMMEND_STATUS_ONLINE,
            'recommendChannel'  => Fz_Ds_Recommend::CHANNEL_ZYB_LIVE,
        );
        
        $list = $this->_objDsRecommend->getListByCond($arrCond, array(), 0, 100);
        //var_dump($list);
        if(empty($list)) {
            return true;
        }
        foreach($list as $k => $live) {
            if(empty($live['extData']['recommendLessonId'])) {
                continue;
            }
            $url = "/flipped/activity/platsaleindex?recommendId=" . $live['recommendId'];
            // 先发关
            $closeRet = MisFz_Tools::pushMsgToLive($live['extData']['recommendLessonId'], $url, Hkzb_Const_FudaoMsgSignal::PUBLIC_STATE_END);
            // 再发开
            $openRet  = MisFz_Tools::pushMsgToLive($live['extData']['recommendLessonId'], $url, Hkzb_Const_FudaoMsgSignal::PUBLIC_STATE_START);
            //var_dump($closeRet, $openRet);
        }
        
//        $this->unlock();
        return true;
    }

    /**
     * @return bool
     */
    public function lock()
    {
        $handle = fopen($this->fileLock, 'w');
        if( false === $handle ) {
            return false;
        }
        return fclose($handle);
    }

    /**
     * 解锁
     * @return void
     */
    public function unlock()
    {
        if( file_exists($this->fileLock) ) {
            @unlink($this->fileLock);
        }
    }

    /**
     * 是否被锁定
     * @return bool
     */
    public function isLock()
    {
        return file_exists($this->fileLock);
    }
    
}