<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-11 10:48:19
 */

namespace App\Http;

/**
 * 预定义的响应状态数据
 *
 * @package App\Http
 */
class ResponseStatus
{
    public const SUCCESS = ["success", "操作成功"];
    public const INVALID_PARAMETER = ["invalid_parameter", "参数缺失或格式有误"];

    public const TOKEN_ERROR = ["token_error", "token错误"];
    public const TASK_NO_COMPLETED = ["task_no_completed", "存在相同任务尚未完成，请等待任务完成后再创建新任务"];

    public const WECHAT_MASS_MESSAGE_TEMPLATE_NOT_FOUND = ["wechat_mess_massage_template_not_found", "微信群发消息模板未找到"];
    public const WECHAT_MASS_MESSAGE_BATCH_NOT_FOUND = ["wechat_mess_massage_batch_not_found", "微信群发消息批次未找到"];
    public const WECHAT_MASS_MESSAGE_BATCH_UNAUTHORIZED = ["wechat_mass_message_batch_unauthorized", "无权访问此微信群发消息批次"];
    public const WECHAT_MASS_MESSAGE_TASK_NOT_FOUND = ["wechat_mess_massage_task_not_found", "微信群发消息任务未找到"];
    public const WECHAT_MASS_MESSAGE_TASK_MODIFY_FAILED = ["wechat_mass_message_task_modify_failed", "微信群发消息任务修改失败"];
    public const WECHAT_MASS_MESSAGE_AUDIT_STATE_INVALID = ["wechat_mass_message_audit_state_invalid", "微信群发消息批次的审核状态无效"];

    public const WECHAT_MASS_MESSAGE_ISERVICE_UNREACHED = ["wechat_mass_message_iservice_unreached", "内部服务请求失败"];
    public const WECHAT_MASS_MESSAGE_ISERVICE_BAD_GATEWAY = ["wechat_mass_message_iservice_bad_gateway", "无法识别微信服务器响应"];
    public const WECHAT_MASS_MESSAGE_ISERVICE_GATEWAY_TIMEOUT = ["wechat_mass_message_iservice_gateway_timeout", "无法连接到微信服务器"];
    public const WECHAT_MASS_MESSAGE_ISERVICE_CODE_UNDEFINED = ["wechat_mass_message_iservice_code_undefined", "内部服务响应返回码有误"];
    public const WECHAT_MASS_MESSAGE_ISERVICE_ALREADY_ONLINE = ["wechat_mass_message_iservice_already_online", "请求的专员已登录网页微信"];
    public const WECHAT_MASS_MESSAGE_ISERVICE_NOT_ONLINE = ["wechat_mass_message_iservice_not_online", "该专员的微信会话未建立"];

    //scrm reponse code
    public const SCRM_SUCCESS = 0;
    public const SCRM_NOLOGIN = 100;
    public const SCRM_WRONG_TOKEN = 101;
    public const SCRM_TOKEN_EXPIRED = 102;
    public const SCRM_USER_NOT_EXIST = 103;
    public const SCRM_WRONG_PARAM = 104;
    public const SCRM_REPEAT_URL = 105;
    public const SCRM_UPDATE_ERROR = 106;
    public const SCRM_DEL_ERROR  = 107;
    public const SCRM_REPEAT_ROLE = 108;
    public const SCRM_ADD_USER_ROLE_ERROR = 109;
    public const SCRM_ROLE_ID_ERROR  = 120;
    public const SCRM_ADD_ROLE_MENU_ERROR = 121;
    public const SCRM_ADD_USER_REP_ERROR = 122;
    public const SCRM_ADD_ROLE_PERMISSION_ERROR =123;
    public const SCRM_CHANGE_REP_ERROR = 124;
    //scrm reponse message
    public const CODE_MESSAGE = [
        self::SCRM_SUCCESS=>'Success',
        self::SCRM_NOLOGIN=>'未登录',
        self::SCRM_WRONG_TOKEN=>'登录令牌无效',
        self::SCRM_TOKEN_EXPIRED => '登录令牌已过期',
        self::SCRM_USER_NOT_EXIST => '用户不存在',
        self::SCRM_WRONG_PARAM =>'参数错误',
        self::SCRM_REPEAT_URL =>'Name不能重复',
        self::SCRM_UPDATE_ERROR =>'更新失败',
        self::SCRM_DEL_ERROR =>'删除失败',
        self::SCRM_REPEAT_ROLE => '角色名重复',
        self::SCRM_ADD_USER_ROLE_ERROR =>'为用户分配角色失败',
        self::SCRM_ROLE_ID_ERROR => '错误的角色ID',
        self::SCRM_ADD_ROLE_MENU_ERROR=>'为角色添加菜单失败',
        self::SCRM_ADD_USER_REP_ERROR => '为用户分配专员失败',
        self::SCRM_ADD_ROLE_PERMISSION_ERROR=>'为角色添加权限失败',
        self::SCRM_CHANGE_REP_ERROR =>'切换专员失败'
    ];


}