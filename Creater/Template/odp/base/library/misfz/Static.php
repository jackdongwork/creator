<?php

/**
 * @file   Static.php
 * @date   2015/7/15 11:50
 * @brief  存放一些静态值
 */
class MisFz_Static
{

    // 管理员角色
    public static $UROLE = array(
        0 => '未知',
        1 => '研发',
        2 => '产品',
        3 => '运营',
        4 => '教务',
        5 => '教研',
        6 => '教培',
        7 => '招聘',
    );

    /*
     * 操作
     */
    static public $ACT = array(
        'add',
        'update',
        'del',
        'leave',
    );

    /*
     * 组默认权限
     */
    static public $PRIVILEGE = array(
        '10000' => '{"rPrivileges":{"subject":[{"gradeId":0, "courseId":0}], "point":[{"gradeId":0, "courseId":0, "pointId":0}]}}',
        '10001' => '{"rPrivileges":{"subject":[{"gradeId":0, "courseId":0}], "point":[{"gradeId":0, "courseId":0, "pointId":0}]}}',
    );

    /**
     * 年级别名 -- 暂未启用
     * @var array
     */
    static public $GRADE_ALIAS  =   array(
        MisFz_Const::GRADE_1   =>  '一年级',
        MisFz_Const::GRADE_2   =>  '二年级',
        MisFz_Const::GRADE_3   =>  '三年级',
        MisFz_Const::GRADE_4   =>  '四年级',
        MisFz_Const::GRADE_5   =>  '五年级',
        MisFz_Const::GRADE_6   =>  '六年级',
    );

    /**
     * 课程别名 -- 暂未启用
     * @var array
     */
    static public $COURSE_TYPE_ALIAS  =   array(
        MisFz_Const::COURSE_TYPE_ENGLISH    =>  '英语',
    );
}
