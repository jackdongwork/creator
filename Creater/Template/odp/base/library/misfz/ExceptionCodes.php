<?php

/**
 * @file   ExceptionCodes.php
 * @author zhengzhiqing@zuoyebang.com
 * @date   2015/7/6 17:43
 */
class MisFz_ExceptionCodes
{

    const DB_ERROR            = 99;

    const UNDEFINED_ERROR     = 1000;
    const PARAM_ERROR         = 1001;
    const NETWORK_ERROR       = 1002;
    const ACTION_NOTFOUND     = 1003;
    const API_ERROR           = 1004;
    const NAPI_ERROR          = 1005;
    const RECORD_NOT_EXISTS   = 1006;
    const EDIT_STATUS_ERROR   = 1007;
    const ERROR_FORMAT_FAILED = 1008;
    const INNER_ERROR         = 1009;
    const DELETE_ERROR        = 1100;


    const USER_NOT_LOGIN      = 2000;
    const USER_NO_PRIVILEGE   = 2001;
    const UID_NULL            = 2002;
    const ADMIN_ADD_ERROR     = 2003;
    const ADMIN_EDIT_ERROR    = 2004;
    const USER_ALREADY_EXISTS = 2005;
    const GROUP_EDIT_ERROR    = 2006;

    const SUBJECT_DEL_ERROR         = 3001;
    const SUBJECT_ADD_ERROR         = 3002;
    const SUBJECT_EDIT_ERROR        = 3003;
    const RCS_DEL_ERROR             = 3901;
    const RCS_ADD_ERROR             = 3902;
    const SUBJECT_EDIT_OPTION_ERROR = 3102;
    const SUBJECT_NOT_EXISTS        = 3103;
    const SUBJECT_ADD_POINT_ERROR   = 3104;
    const SUBJECT_DEL_POINT_ERROR   = 3105;

    const SUBJECTTMP_TO_SUBJECTTBL_ERROR = 4001;
    const SUBJECTTMP_NOT_EXISTS          = 4002;
    const SUBJECTTMP_HAD_CHECK           = 4003;

    const CASE_ADD_ERROR = 5001;

    const POINT_EDIT_ERROR           = 3201;
    const POINT_ADD_ERROR            = 3202;
    const TREE_NODE_EDIT_ERROR       = 3203;
    const TREE_NODE_ADD_ERROR        = 3204;
    const TREE_NODE_EXISTS           = 3205;
    const POINT_NOT_EXISTS           = 3206;
    const UPDATE_POINT_SUBJECT_ERROR = 3207;
    const TREE_NODE_TID_NO_NULL      = 3208;
    const TREE_NODE_DEL_DISABLE      = 3209;

    const LABEL_NOT_EXISTS    = 3301;
    const LABEL_ACTION_FAILED = 3302;

    const TASK_EDIT_ERROR             = 4101;
    const TASK_ADD_ERROR              = 4102;
    const TASK_MOUNT_EDITER_NUM_ERROR = 4103;
    const TASK_NOT_EXISTS             = 4104;
    const TASK_IS_EXPIRED             = 4105;
    const TASK_SET_POOL_FAILED        = 4106;
    const TASK_POOL_FINISH            = 4107;
    const TASK_ADD_POOL_WARNING       = 4108;
    const TASK_USER_EDIT_ERROR        = 4109;
    const TASK_USER_ADD_ERROR         = 4110;
    const TASK_DEL_ERROR              = 4111;
    const TASK_UNIT_BING_DEL_ERROR    = 4112;

    const PIC_IS_NOT_UPLOADED_FILE        = 11000; //非上传文件
    const ERROR_UPLOAD_ERR_INI_SIZE       = 11001; //超过php.ini限制
    const ERROR_UPLOAD_ERR_FORM_SIZE      = 11002; //超过form表单限制
    const ERROR_UPLOAD_ERR_PARTIAL        = 11003; //文件只有部分被上传
    const ERROR_UPLOAD_ERR_NO_FILE        = 11004; //没有文件被上传
    const ERROR_UPLOAD_ERR_NO_TMP_DIR     = 11005; //无临时文件目录
    const ERROR_UPLOAD_ERR_CANT_WRITE     = 11006; //写入失败
    const ERROR_UPLOAD_ERR_EXTENSION      = 11007; //扩展导致图片上传失败
    const ERROR_UPLOAD_EXCEED_MAX         = 11008; //图片大小超过限制
    const ERROR_IS_NOT_A_PICTURE          = 11009; //图片格式错误
    const ERROR_UPLOAD_PIC_FAILED         = 11010; //图片上传失败
    const ERROR_UPLOAD_FILE_FAILED        = 11011; //文件上传失败
    const ERROR_UPLOAD_FILE_FORMAT_FAILED = 11012; //文件上传失败

    const ERROR_NS_SOCKET_CONNECTION = 91001; //检索服务连接失败
    const ERROR_NS_WRITE_CONNECTION  = 91002; //检索服务写入失败
    const ERROR_NS_READ_CONNECTION   = 91003; //检索服务读取失败

    const USERINFO_ERROR      = 20001;
    const OVER_MAX_EXAM_TIMES = 20002;
    const ALREADY_DONE_EXAM   = 20003;

    const OVER_MAX_LECTURE_TIMES = 20004;
    const ERRNO_USER_NO_PRIVILEGE=30001;


    // sms
    const ERROR_SMS_PARAM              = 92001; //短信模板参数有误
    const ERROR_SMS_LENGTH_ERROR       = 92002; //短信超长
    const ERROR_SMS_PHONE_FORMAT_ERROR = 92003; //存在不合法手机号码


	// 教培管理
	const PERSONNEL_UID_EXIST          = 5001; //当前手机号绑定用户已存在
	const PERSONNEL_NOT_EXIST          = 5002; //人员详细信息不存在
	const PERSONNEL_DEL_ERROR          = 5003; //人员删除失败
	const PERSONNEL_UPDATE_ERROR       = 5004; //人员更新失败

    //校区
    const CAMPUS_ID_REQUIRED           = 6000; //缺少校區 ID
    const CAMPUS_NAME_REQUIRED         = 6001; //缺少校區名稱
    const CAMPUS_NAME_ALREADY_EXISTS   = 6002; //校區名稱已經存在
    const TRAINER_ID_REQUIRED          = 6003; //缺少培訓師 ID
    const CAMPUS_NOT_EXISTS            = 6004; //小區不存在
    const TRAINER_NOT_EXISTS           = 6005; //培訓師不存在
    const EDUAD_ID_REQUIRED            = 6006; //缺少教務 ID
    const EDUAD_NOT_EXISTS             = 6007; //教務不存在
    const TEACHER_REQUIRED             = 6008; //缺少教师 ID

    //学生相关
    const STUDENT_NOT_EXISTS           = 7009; //学生不存在

	const COURSE_ADD_ERROR               = 7001; // 创建课程失败
	const UNIT_UPDATE_COURSE_LIST_ERROR  = 7002; // 更新课程的单元列表
	const COURSE_NOT_EXISTS              = 7003; // 课程信息不存在
	const UNIT_NOT_EXISTS                = 7004; // 单元信息不存在
	const COURSE_ID_NOT_EXISTS           = 7005; // 要删除的课程id不存在
	const UNIT_ID_LIST_DELETE_ERROR      = 7006; // 置空课程的单元列表失败
	const COURSE_DELETE_ERROR            = 7007; // 删除课程失败
	const UNIT_UPDATE_ERROR              = 7008; // 更新单元失败
	const COURSE_UPDATE_ERROR            = 7010; // 修改课程失败
	const TASK_UNBIND_ERROR              = 7011; // 原有任务解绑单元失败
	const TASK_BIND_UNIT_ERROR           = 7012; // 任务绑定单元失败
	const UNIT_BIND_TASK_ERROR           = 7013; // 单元配置学习任务失败
	const COURSE_ADD_EXISTS              = 7014; // 要添加的课程已存在


	//课程活动
	const TYPE_ERROR                     = 8001; // 活动类型错误
	const COURSE_ACTIVITY_ADD_ERROR      = 8002; // 课程活动添加失败
	const COURSE_ACTIVITY_DELETE_ERROR   = 8003; // 课程活动删除失败
	const COURSE_ACTIVITY_NOT_EXISTS     = 8004; // 课程活动不存在
	const COURSE_ACTIVITY_UPDATE_ERROR   = 8005; // 课程活动更新失败
	const COURSE_ACTIVITY_STATUS_ERROR   = 8006; // 课程活动状态更新失败

	//打卡活动
	const CLOCK_ACTIVITY_DELETE_ERROR    = 9001; // 打卡活动删除失败
	const CLOCK_ACTIVITY_NOT_EXISTS      = 9002; // 打卡活动不存在
	const CLOCK_ACTIVITY_ADD_ERROR       = 9003; // 打卡活动添加失败
	const CLOCK_ACTIVITY_STATUS_ERROR    = 9004; // 打卡活动状态更新失败
	const CLOCK_ACTIVITY_UPDATE_ERROR    = 9005; // 课程活动更新失败
	const CLOCK_TASK_BIND_ERROR          = 9006; // 打卡任务绑定错误

	//打卡oneday
	const CLOCK_DAY_DELETE_ERROR         = 9007; // 打卡活动删除失败
	const CLOCK_DAY_NOT_EXISTS           = 9008; // 打卡活动不存在
	const CLOCK_DAY_ADD_ERROR            = 9009; // 打卡活动添加失败
	const CLOCK_DAY_STATUS_ERROR         = 9010; // 打卡活动状态更新失败
	const CLOCK_DAY_UPDATE_ERROR         = 9011; // 课程活动更新失败
	const CLOCK_DAY_DATE_EXISTS          = 9012; // 当前打卡日期已存在

    const AD_PUSH_FAIL                   = 10000; //广告推送失败

    const REFUND_NOT_FOUND               = 11000; // 未找到退款信息

	const SERVICE_ITEM_ADD_ERROR         = 12000; // 服务项添加失败
	const SERVICE_ITEM_UPDATE_ERROR      = 12001; // 服务项更新失败
	const SERVICE_ITEM_NOT_EXISTS        = 12002; // 服务项不存在
	const SERVICE_ITEM_STATUS_ERROR      = 12003; // 服务项状态更新失败
	const SERVICE_ITEM_STATUS_BAN        = 12004; // 服务项在商品中有配置,不能删除或编辑哦
	const SERVICE_ITEM_STATUS_BAND       = 12005; // 服务项在商品中有配置,不能删除或编辑哦

    //试卷相关
    const EXAM_DATA_ERROR = 13000; //试卷数据错误


	public static $errMsg = array(
        // system
        self::UNDEFINED_ERROR           => '未知错误',
        self::PARAM_ERROR               => '参数有误',
        self::NETWORK_ERROR             => '网络繁忙',
        self::ACTION_NOTFOUND           => '未定义操作',
        self::API_ERROR                 => 'API接口调用错误',
        self::NAPI_ERROR                => 'NAPI接口调用错误',
        self::RECORD_NOT_EXISTS         => '数据不存在',
        self::EDIT_STATUS_ERROR         => '暂不可以改动',
        self::ERROR_FORMAT_FAILED       => '数据格式有误',
        self::INNER_ERROR               =>  '内部错误',
        self::DELETE_ERROR              =>  '删除失败',

        // db
        self::DB_ERROR                  => '数据库连接初始化失败',


        // user
        self::USER_NOT_LOGIN            => '用户未登录',
        self::USER_NO_PRIVILEGE         => '权限不足',
        self::UID_NULL                  => '非作业帮帐号',
        self::ADMIN_EDIT_ERROR          => '用户信息编辑失败',
        self::ADMIN_ADD_ERROR           => '用户添加失败',
        self::USER_ALREADY_EXISTS       => '用户已存在',
        self::GROUP_EDIT_ERROR          => '组信息编辑失败',

        // data
        self::SUBJECT_DEL_ERROR         => '题目删除失败',
        self::SUBJECT_ADD_ERROR         => '题目添加失败',
        self::SUBJECT_EDIT_ERROR        => '题目编辑失败',
        self::RCS_DEL_ERROR             => 'RCS检索删除失败',
        self::RCS_ADD_ERROR             => 'RCS检索添加失败',
        self::SUBJECT_EDIT_OPTION_ERROR => '题目暂时无法操作',
        self::SUBJECT_NOT_EXISTS        => '题目数据不存在',
        self::SUBJECT_ADD_POINT_ERROR   => '为题目添加知识点失败',
        self::SUBJECT_DEL_POINT_ERROR   => '为题目删除知识点失败',

        self::SUBJECTTMP_TO_SUBJECTTBL_ERROR => '题目更新到正式数据表失败',
        self::SUBJECTTMP_NOT_EXISTS          => '编辑数据不存在',
        self::SUBJECTTMP_HAD_CHECK           => '数据已经审核通过，不要重复操作',

        self::CASE_ADD_ERROR => '添加反馈反馈',

        self::POINT_EDIT_ERROR           => '知识点编辑失败',
        self::POINT_ADD_ERROR            => '知识点添加失败',
        self::TREE_NODE_EDIT_ERROR       => '节点编辑失败',
        self::TREE_NODE_ADD_ERROR        => '节点添加失败',
        self::TREE_NODE_EXISTS           => '节点已存在',
        self::POINT_NOT_EXISTS           => '知识点不存在',
        self::UPDATE_POINT_SUBJECT_ERROR => '更新知识点下题目失败',
        self::TREE_NODE_TID_NO_NULL      => '知识点下还有题目，清空后再试',
        self::TREE_NODE_DEL_DISABLE      => '此知识点不可以删除',

        self::LABEL_NOT_EXISTS    => '标签不存在',
        self::LABEL_ACTION_FAILED => '更新题目标签失败',

        self::TASK_ADD_ERROR                  => '任务添加失败',
        self::TASK_EDIT_ERROR                 => '任务编辑失败',
        self::TASK_MOUNT_EDITER_NUM_ERROR     => '挂题任务只允许添加一个编辑人员',
        self::TASK_NOT_EXISTS                 => '任务不存在',
        self::TASK_IS_EXPIRED                 => '任务未开始或已结束',
        self::TASK_SET_POOL_FAILED            => '获取任务数据失败',
        self::TASK_POOL_FINISH                => '任务分配完毕',
        self::TASK_ADD_POOL_WARNING           => 'Tid只可以在添加任务时上传',
        self::TASK_USER_EDIT_ERROR            => '编辑任务用户失败',
        self::TASK_DEL_ERROR                  => '任务删除失败',
        self::TASK_UNIT_BING_DEL_ERROR        =>'已绑定单元不能删除',

        // upload
        self::PIC_IS_NOT_UPLOADED_FILE        => '文件类型有误',
        self::ERROR_UPLOAD_ERR_INI_SIZE       => '上传文件大小受限',
        self::ERROR_UPLOAD_ERR_FORM_SIZE      => '上传文件大小有误',
        self::ERROR_UPLOAD_ERR_PARTIAL        => '文件有误',
        self::ERROR_UPLOAD_ERR_NO_FILE        => '文件有误',
        self::ERROR_UPLOAD_ERR_NO_TMP_DIR     => '无临时文件夹',
        self::ERROR_UPLOAD_ERR_CANT_WRITE     => '无法保存文件',
        self::ERROR_UPLOAD_ERR_EXTENSION      => '图片类型错误',
        self::ERROR_UPLOAD_EXCEED_MAX         => '上传超时',
        self::ERROR_IS_NOT_A_PICTURE          => '文件不是图片',
        self::ERROR_UPLOAD_PIC_FAILED         => '图片上传失败',
        self::ERROR_UPLOAD_FILE_FAILED        => '文件上传失败',
        self::ERROR_UPLOAD_FILE_FORMAT_FAILED => '文件格式有问题',

        // tools
        self::ERROR_NS_SOCKET_CONNECTION      => 'NS连接失败',
        self::ERROR_NS_WRITE_CONNECTION       => 'NS写失败',
        self::ERROR_NS_READ_CONNECTION        => 'NS读失败',

        // sms
        self::ERROR_SMS_PARAM                 => '短信模板参数有误',
        self::ERROR_SMS_LENGTH_ERROR          => '短信超长',
        self::ERROR_SMS_PHONE_FORMAT_ERROR    => '存在不合法手机号码',
        //exam
        self::USERINFO_ERROR                  => '用户信息错误',
        self::ALREADY_DONE_EXAM               => '您已经参加过考试或超过最大考试次数',
        self::OVER_MAX_EXAM_TIMES             => '您已超过最大考试次数',
        self::OVER_MAX_LECTURE_TIMES          => '您已超过最大试讲次数',

		//教培管理
		self::PERSONNEL_UID_EXIST             => '手机号绑定用户已存在',
		self::PERSONNEL_NOT_EXIST             => '人员详细信息不存在',
		self::PERSONNEL_DEL_ERROR             => '人员删除失败',
		self::PERSONNEL_UPDATE_ERROR          => '人员更新失败',

        //校区管理
        self::CAMPUS_ID_REQUIRED              => '缺少校区 ID',
        self::CAMPUS_NAME_REQUIRED            => '缺少校区名称',
        self::CAMPUS_NAME_ALREADY_EXISTS      => '校区名称已经存在',
        self::TRAINER_ID_REQUIRED             => '缺少培训师 ID',
        self::TRAINER_ID_REQUIRED             => '缺少培训师 ID',
        self::CAMPUS_NOT_EXISTS               => '校区不存在',
        self::TRAINER_NOT_EXISTS              => '培训师不存在',
        self::EDUAD_ID_REQUIRED               => '缺少教務 ID',
        self::EDUAD_NOT_EXISTS                => '教務不存在',
        self::TEACHER_REQUIRED                => '缺少教师 ID',

        //学生
        self::STUDENT_NOT_EXISTS              => '学生不存在',


        self::COURSE_ADD_ERROR                => '创建课程失败',
        self::UNIT_UPDATE_COURSE_LIST_ERROR   => '更新课程的单元列表失败',
        self::COURSE_NOT_EXISTS               => '课程信息不存在',
        self::UNIT_NOT_EXISTS                 => '单元信息不存在',
        self::COURSE_ID_NOT_EXISTS            => '要删除的课程id不存在',
        self::UNIT_ID_LIST_DELETE_ERROR       => '置空课程的单元列表失败',
        self::COURSE_DELETE_ERROR             => '删除课程失败',
        self::UNIT_UPDATE_ERROR               => '更新单元失败',
        self::COURSE_ADD_EXISTS               => '要添加的课程已存在',
        self::COURSE_UPDATE_ERROR             => '修改课程失败',
        self::TASK_UNBIND_ERROR               => '原有任务解绑单元失败',
        self::TASK_BIND_UNIT_ERROR            => '任务绑定单元失败',
        self::UNIT_BIND_TASK_ERROR            => '单元配置学习任务失败',


		//课程活动
		self::TYPE_ERROR                      => '活动类型错误',
		self::COURSE_ACTIVITY_ADD_ERROR       => '课程活动添加失败',
		self::COURSE_ACTIVITY_DELETE_ERROR    => '课程活动删除失败',
		self::COURSE_ACTIVITY_NOT_EXISTS      => '课程活动不存在',
		self::COURSE_ACTIVITY_UPDATE_ERROR    => '课程活动更新失败',
		self::COURSE_ACTIVITY_STATUS_ERROR    => '课程活动状态错误',

		//打卡活动
		self::CLOCK_ACTIVITY_DELETE_ERROR     => '打卡活动删除失败',
		self::CLOCK_ACTIVITY_NOT_EXISTS       => '打卡活动不存在',
		self::CLOCK_ACTIVITY_ADD_ERROR        => '打卡活动添加失败',
		self::CLOCK_ACTIVITY_UPDATE_ERROR     => '打卡活动更新失败',
		self::CLOCK_ACTIVITY_STATUS_ERROR     => '打卡活动状态错误',
		self::CLOCK_TASK_BIND_ERROR           => '打卡任务绑定错误',


		//打卡活动oneday
		self::CLOCK_DAY_DELETE_ERROR     	  => '打卡oneday删除失败',
		self::CLOCK_DAY_NOT_EXISTS            => '打卡oneday不存在',
		self::CLOCK_DAY_ADD_ERROR             => '打卡oneday添加失败',
		self::CLOCK_DAY_UPDATE_ERROR          => '打卡oneday更新失败',
		self::CLOCK_DAY_STATUS_ERROR          => '打卡oneday状态错误',
		self::CLOCK_DAY_DATE_EXISTS           => '当前打卡日期已存在',

        self::ERRNO_USER_NO_PRIVILEGE         => '用户没有这个权限',

        self::REFUND_NOT_FOUND                => '未找到退款信息',

		//服务项
		self::SERVICE_ITEM_ADD_ERROR        => '服务项添加失败',
		self::SERVICE_ITEM_UPDATE_ERROR     => '服务项更新失败',
		self::SERVICE_ITEM_STATUS_ERROR     => '服务项状态错误',
		self::SERVICE_ITEM_STATUS_BAN       => '服务项在商品中有配置,不能删除或编辑哦~',
		self::SERVICE_ITEM_STATUS_BAND      => '服务项在商品中有配置,不能下架哦~',

        //试卷相关
        self::EXAM_DATA_ERROR               => '试卷数据错误',

	);

    /**
     * 获取错误信息 utf8编码
     *
     * 2017-02-21 增加根据规则能获取app内部自定义的错误码
     *
     * @param $errno
     */
    public static function getErrMsg($errno)
    {
        if (isset(self::$errMsg[$errno])) {
            return self::$errMsg[$errno];
        } else {
            $appClass = sprintf("%s_ExceptionCodes", ucfirst(MAIN_APP));            # 获取app内部按照规则自订的错误码信息
            if (class_exists($appClass) && isset($appClass::$appErrMsg[$errno])) {
                return $appClass::$appErrMsg[$errno];
            }
        }

        return '未知错误';
    }
}