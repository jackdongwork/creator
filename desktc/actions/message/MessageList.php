<?php
/**
 * file  : MessageList.php
 * author: chenzhiwen
 * date  : 2018/08/15
 * brief :
 */
class Action_MessageList extends DeskTc_Action_Base
{
    public function invoke()
    {
        $arrInput = array(
            'userInfo'    => $this->_userInfo,
        );

        Hk_Util_Log::start('log');

        $obj = new obj();

        $this->_outPut['data'] = $obj->execute($arrInput);
        Hk_Util_Log::stop('log');
    }
}