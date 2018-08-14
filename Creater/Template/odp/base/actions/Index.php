<?php

/**
 * @name Action_MisFz_Index
 * @desc /mis/
 */
class Action_Index extends MisFz_Action_Base
{
    public function invoke() {
        // input
        $arrInput = array(
            'uid'   => intval($this->_userInfo['uid']),
            'uname' => strval($this->_userInfo['uname']),
        );

        $objModel   = new MisFz_Privileges($this->_userInfo);
        $userModels = $objModel->getModels();

        // output
        $this->_setTpl         = true;
        $this->_outPut['tpl']  = 'tutor/page/index/index.tpl';
        $this->_outPut['data'] = array(
            'uid'         => $arrInput['uid'],
            'uname'       => $arrInput['uname'],
            'menuList'    => $userModels,
            'sysTypeList' => Hk_Util_Category::$SYS_TYPE_ARRAY,
        );
    }
}
