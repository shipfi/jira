<?php
namespace mysoft\helpers;

/**
 * errorcode说明
 * <ul>
 *    <li>-10001: 未知异常</li>  
 *    <li>-400: 不合法字段</li>  
 *    <li>-40001: 获取access_token时Secret错误，或者access_token无效</li>  
 *    <li>-40008: 不合法的消息类型</li>
 *    <li>-40013: 不合法的corpid</li>
 *    <li>-40014: 不合法的access_token</li>
 *    <li>-40056: 不合法的agentid</li>
 *    <li>-40201: 不合法的tenantid</li>
 *    <li>-410: 缺少字段</li>  
 *    <li>-41001: 缺少access_token参数</li>
 *    <li>-41002: 缺少corpid参数</li>
 *    <li>-41003: 缺少refresh_token参数</li>
 *    <li>-41004: 缺少secret参数</li>
 *    <li>-41005: 缺少多媒体文件数据</li>
 *    <li>-41006: 缺少media_id参数</li>
 *    <li>-41007: 缺少子菜单数据</li>
 *    <li>-41008: 缺少oauth code</li>
 *    <li>-41009: 缺少UserID</li>
 *    <li>-41010: 缺少url</li>
 *    <li>-41011: 缺少agentid</li>
 *    <li>-41012: 缺少应用头像mediaid</li>
 *    <li>-41013: 缺少应用名字</li>
 *    <li>-41014: 缺少应用描述</li>
 *    <li>-41015: 缺少Content</li>
 *    <li>-41016: 缺少标题</li>
 *    <li>-41017: 缺少标签ID</li>
 *    <li>-41018: 缺少标签名字</li>
 *    <li>-41021: 缺少suiteid</li>
 *    <li>-41022: 缺少suitetoken</li>
 *    <li>-41023: 缺少suiteticket</li>
 *    <li>-41024: 缺少suitesecret</li>
 *    <li>-41025: 缺少永久授权码</li>
 *    <li>-41090: 缺少tenantid</li>
 *    <li>-41091: 缺少msgtype</li>
 *    <li>-41092: 缺少消息体</li>
 *    <li>-420:过期</li>
 *    <li>-42001:access_token超时</li>
 *    <li>-600:字段不正确</li>
 *    <li>-60031:租户id不存在，请检查租户信息正确性！</li>
 * </ul>
 * @author wank
 * 
 */
class ErrorCodeHelper {
    const OK = 0;
    const FAIL = 10001;
    
    const ERRJSON = 41000;
    
    /**
     * 缺少access_token参数
     */    
    const INVALID_ACCESSTOKEN = 40001;
    const INVALID_CORPID = 40013;
    const INVALID_SECRET = 40001;
    const INVALID_AGENTID = 40056;
    const INVALID_TENANTID = 40201;
    const ILLEGAL_ACCESSTOKEN = 40014;
    const INVALID_APPID = 40001; //新增20151209,by yangzhen
    
    
    const MISS_ACCESSTOKEN = 41001;
    const MISS_CORPID = 41002;    
    const MISS_SECRET = 41004; 
    const MISS_USERID = 41009;
    const MISS_AGENTID = 41011;
    const MISS_TENANTID = 41090;
    const MISS_MSGTYPE = 41091;
    const MISS_MSGBODY = 41092;    
    
    const EXPIRE_ACCESSTOKEN = 42001;
    
    const ILLEGAL_TENANTID = 60031;
    
    protected static $ERROR = [        
        self::OK => 'OK',
        self::ERRJSON => 'JSON格式解析失败，请检查JSON体结构是否正确（在线校验地址：http://www.bejson.com/）',
        self::INVALID_ACCESSTOKEN => '获取access_token时Secret错误，或者access_token无效',
        self::INVALID_CORPID => '不合法的corpid',
        self::INVALID_SECRET => '获取access_token时Secret错误，或者access_token无效',
        self::INVALID_AGENTID => '不合法的agentid',
        self::ILLEGAL_TENANTID => '不合法的tenantid',
        self::ILLEGAL_ACCESSTOKEN => '不合法的access_token',
        self::MISS_ACCESSTOKEN => '缺少access_token参数',
        self::MISS_CORPID => '缺少corpid参数',
        self::MISS_SECRET => '缺少corpid参数',
        self::MISS_USERID => '缺少UserID',
        self::MISS_AGENTID => '缺少agentid',
        self::MISS_MSGTYPE => '缺少msgtype',
        self::MISS_MSGBODY => '缺少消息体',
        self::MISS_TENANTID => '缺少tenantid',
        self::EXPIRE_ACCESSTOKEN => 'access_token超时',
        self::ILLEGAL_TENANTID => '租户id不存在，请检查租户信息正确性！',
        self::INVALID_APPID=>'不合法的appid'
    ];
    
    /**
     * 
     * @param int $errcode  错误码
     * @return string 错误码对应的错误描述
     */
    public static function getErrmsg($errcode){
        return self::$ERROR[$errcode];
    }
    
}