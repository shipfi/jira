<?php
namespace mysoft\wx;

use mysoft\wx\Prpcrypt;
use mysoft\http\Curl;
use mysoft\helpers\String;
use mysoft\http\mysoft\http;


/**
 * @version 0.1
 * @author fangl 2015.7
 *
 */
class QyHelper {
    
    const GET_TOKEN = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=CORPID&corpsecret=SECRET';
    
    static function getToken($corpId,$corpSecret) {
        $curl = new Curl();
        $res = $curl->get(str_replace('SECRET', $corpSecret, str_replace('CORPID', $corpId, static::GET_TOKEN)));
        return json_decode($res,true);
    }
    
    /**
     * 获取签名
     * @param string $token
     * @param string $timestamp
     * @param string $nonce
     * @param string $encrypt_msg
     * @return string sha1加密签名串
     */
    static function getSig($token, $timestamp, $nonce, $encrypt_msg) {
        $array = array($encrypt_msg, $token, $timestamp, $nonce);
        sort($array, SORT_STRING);
        $str = implode($array);
        $sha1 = sha1($str);
        return $sha1;
    }
    
    /**
     * 验证签名
     * @param string $msg_signature
     * @param string $token
     * @param string $timestamp
     * @param string $nonce
     * @param string $encrypt_msg
     * @return boolean
     */
    static function isSigValid($msg_signature, $token, $timestamp, $nonce, $encrypt_msg) {
        return strcmp(self::getSig($token, $timestamp, $nonce, $encrypt_msg),$msg_signature) == 0;
    }
    
    /**
     * 将xml格式的消息体转换为对象
     * @param string $msg_xml
     * @return SimpleXMLElement
     */
    static function toObj($msg_xml) {
        return simplexml_load_string ( $msg_xml, 'SimpleXMLElement', LIBXML_NOCDATA );
    }
    
    /**
     * 将obj转换为json字符串
     * @param object $msg_obj
     * @return string
     */
    static function toStr($msg_obj) {
        return is_object($msg_obj)?json_encode(self::objectToArray($msg_obj)):$msg_obj;
    }
    
    /**
     * 将obj转换为关联数组
     * @param object $obj
     * @return array
     */
    static function objectToArray($obj) {
        $_arr= is_object($obj) ? get_object_vars($obj) : $obj;
        foreach($_arr as $key=> $val)
        {
            $val= (is_array($val) || is_object($val)) ? self::objectToArray($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
    
    static function isWxRetOk($ret) {
        return isset($ret['errcode']) && $ret['errcode'] == 0 || !isset($ret['errcode']);
    }
    
    /**
     * 解密微信post消息体
     * @param string|obj &$msg 如果解密不成功时，通过$msg返回解密后的内容
     * @param string $aeskey
     * @param string $corpid false while not valid
     * @return SimpleXMLElement|NULL
     */
    static function decryptMsg(&$msg, $aeskey, $corpid) {
        if(!is_object($msg)) {
            $msg_obj = QyHelper::toObj($msg);
        }
        else $msg_obj = $msg;
        $prpcrypt = new Prpcrypt($aeskey);
        $content = $prpcrypt->decrypt($msg_obj->Encrypt, $corpid, $corpid != false);
        if($prpcrypt->isSuccess()) {
            return self::toObj($content);
        }
        else {
            if(!empty($content)) {
                $msg = $content;
            }
            else $msg = $prpcrypt->getErr()->message;
            if(YII_DEBUG == true) $prpcrypt->printErr();
            return null;
        }
    }
    
    /**
     * 加密待回复给微信的消息体
     * @param string $msg_xml
     * @param string $corpid
     * @param string $aeskey
     * @param string $token
     * @param string $timestamp
     * @param string $nonce
     * @return string|NULL
     */
    static function encryptMsg($msg_xml, $corpid, $aeskey, $token, $timestamp, $nonce) {
        $prpcrypt = new Prpcrypt($aeskey);
        $encrypt = $prpcrypt->encrypt($msg_xml, $corpid);
        if($prpcrypt->isSuccess()) {
            $signature = self::getSig($token, $timestamp, $nonce, $encrypt);
            $format = '<xml><Encrypt><![CDATA[%s]]></Encrypt><MsgSignature><![CDATA[%s]]></MsgSignature><TimeStamp>%s</TimeStamp><Nonce><![CDATA[%s]]></Nonce></xml>';
            return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
        }
        else {
            if(YII_DEBUG == true) $prpcrypt->printErr();
            return null;
        }
    }
    
    /******* 套件相关接口，参考：http://qydev.weixin.qq.com/wiki/index.php?title=%E7%AC%AC%E4%B8%89%E6%96%B9%E5%BA%94%E7%94%A8%E6%8E%A5%E5%8F%A3%E8%AF%B4%E6%98%8E#.E8.AE.BE.E7.BD.AE.E6.8E.88.E6.9D.83.E9.85.8D.E7.BD.AE */

//     const SUITE_API_BASE = 'https://qyapi.weixin.qq.com/cgi-bin/service';
    
//     static function callSuiteApi($api_name,$arg,$method='post') {
//         $curl = new Curl();
//         $ret = $curl->$method(self::SUITE_API_BASE.$api_name,json_encode($arg));
//         if($curl->getStatus() == 200) {
//             return json_decode($ret,true);
//         }
//         else return null;
//     }
    
//     static function resolveCallParams($method) {
//         $ref = new \ReflectionClass($method);
//     }
    
//     static function test($a,$b) {
//         die(get_called_class());
//         list($api_name,$arg) = self::resolveCallParams(self,get_called_class());
//         return self::callSuiteApi($api_name, $arg);
//     }
    
    const GET_SUIT_TOKEN = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token';
    const GET_PRE_AUTH_CODE = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token=';
    const SET_SESSION_INFO = 'https://qyapi.weixin.qq.com/cgi-bin/service/set_session_info?suite_access_token=';
    const SUITE_LOGIN_PAGE = 'https://qy.weixin.qq.com/cgi-bin/loginpage';
    const SUITE_PERMANENTT_CODE = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code?suite_access_token=';
    const GET_AUTH_INFO = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info?suite_access_token=';
    const GET_AGENT = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_agent?suite_access_token=';
    const SET_AGENT = 'https://qyapi.weixin.qq.com/cgi-bin/service/set_agent?suite_access_token=';
    const GET_CORP_TOKEN = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token?suite_access_token=';
    
    /**
     * 获取套件的access_token，用于访问接口
     * @param $suite_id
     * @param $suite_secret
     * @param string $suite_ticket 由callback定时推送套件注册回调地址
     * @return mixed
     */
    static function getSuiteToken($suite_id, $suite_secret, $suite_ticket) {
        $curl = new Curl();
        return json_decode($curl->post(self::GET_SUIT_TOKEN, json_encode(['suite_id'=>$suite_id,'suite_secret'=>$suite_secret,'suite_ticket'=>$suite_ticket])),true);
    }
    
    /**
     * 获取预授权码，用于生成企业应用管理员授权页面链接
     * @param string $sat   由::getSuiteToken中获取的access_token，为字符串
     * @param $suite_id
     * @param array $appids
     * @return mixed
     */
    static function getPreAuthCode($sat, $suite_id, $appids) {
        $appids = (array)$appids;
        $curl = new Curl();
        $ret = $curl->post(self::GET_PRE_AUTH_CODE.$sat, json_encode(['suite_id'=>$suite_id,'appids'=>$appids]));
        return json_decode($ret, true);
    }
    
    /**
     * 设置会话信息，目前与::getPreAuthCode配合用于设置授权过程中的可选择应用列表
     * @param $sat
     * @param $pre_auth_code
     * @param $appids
     * @return mixed
     */
    static function setSessionInfo($sat, $pre_auth_code, $appids) {
        $curl = new Curl();
        $ret = $curl->post(self::SET_SESSION_INFO.$sat,json_encode(['pre_auth_code'=>$pre_auth_code,'session_info'=>['appid'=>$appids]]));
        return json_decode($ret, true);
    }
    
    /**
     * 获取套件授权页面地址
     * @param $suite_id
     * @param $pre_auth_code
     * @param $redirect_uri
     * @param $state
     * @return string
     */
    static function getSuiteLoginPage($suite_id, $pre_auth_code, $redirect_uri, $state) {
        $query = ['suite_id'=>$suite_id,'pre_auth_code'=>$pre_auth_code,'redirect_uri'=>$redirect_uri,'state'=>$state];
        return self::SUITE_LOGIN_PAGE.'?'.http_build_query($query);
    }
    
    /**
     * 获取永久授权码
     * @param $sat
     * @param $auth_code
     * @param $suite_id
     * @return mixed
     */
    static function getPermanentCode($sat, $auth_code, $suite_id) {
        $curl = new Curl();
        $ret = $curl->post(self::SUITE_PERMANENTT_CODE.$sat,json_encode(['suite_id'=>$suite_id,'auth_code'=>$auth_code]));
        return json_decode($ret, true);
    }
    
    /**
     * 获取授权信息
     * @param $sat
     * @param $suite_id
     * @param $auth_corpid
     * @param $permanent_code
     * @return mixed
     */
    static function getAuthInfo($sat, $suite_id, $auth_corpid, $permanent_code) {
        $curl = new Curl();
        $ret = $curl->post(self::GET_AUTH_INFO.$sat, json_encode(['suite_id'=>$suite_id,'auth_corpid'=>$auth_corpid,'permanent_code'=>$permanent_code]));
        return json_decode($ret, true);
    }
    
    /**
     * 获取企业应用信息（要求应用必须被授权或者托管）
     * @param $sat
     * @param $suite_id
     * @param $auth_corpid
     * @param $permanent_code
     * @param $agentid
     * @return mixed
     */
    static function getAgent($sat, $suite_id, $auth_corpid, $permanent_code, $agentid ) {
        $curl = new Curl();
        $ret = $curl->post(self::GET_AGENT.$sat, json_encode(['suite_id'=>$suite_id, 'auth_corpid'=>$auth_corpid, 'permanent_code'=>$permanent_code, 'agentid'=>$agentid]));
        return json_decode($ret, true);
    }
    
    /**
     * 设置企业应用信息
     * @param $sat
     * @param $suite_id
     * @param $auth_corpid
     * @param $permanet_code
     * @param array $agent 应用ID
     *      "agent":  {
        		"agentid": 5,
        		"report_location_flag": 0,
        		"logo_mediaid": "xxxxx",
        		"name": "NAME",
        		"description": "DESC",
        		"redirect_domain": "xxxxxx",
        		"isreportuser":0,
                       "isreportenter":0
        	 }
     * @return mixed
     */
    static function setAgent($sat, $suite_id, $auth_corpid, $permanet_code, $agent) {
        $curl = new Curl();
        $ret = $curl->post(self::SET_AGENT.$sat, json_encode(['suite_id'=>$suite_id, 'auth_corpid'=>$auth_corpid, 'permanent_code'=>$permanet_code, 'agent'=>$agent]));
        return json_decode($ret, true);
    }
    
    static function getCorpToken($sat, $suite_id, $auth_corpid, $permanent_code) {
        $curl = new Curl();
        $ret = $curl->post(self::GET_CORP_TOKEN.$sat, json_encode(['suite_id'=>$suite_id, 'auth_corpid'=>$auth_corpid,'permanent_code'=>$permanent_code]));
        return json_decode($ret, true);
    }
    
    const CREATE_MENU = 'https://qyapi.weixin.qq.com/cgi-bin/menu/create?';
    static function createAgentMenu($corp_access_token,$agentid,$btncfg) {
        $btncfg = String::jsonEncode($btncfg);
        $curl = new Curl();
        $ret = $curl->post(self::CREATE_MENU.http_build_query(['access_token'=>$corp_access_token,'agentid'=>$agentid]), $btncfg);
        return json_decode($ret, true);
    }
    
    const DEL_MENU = 'https://qyapi.weixin.qq.com/cgi-bin/menu/delete?';
    static function delAgentMenu($corp_access_token,$agentid) {
        $curl = new Curl();
        $ret = $curl->get(self::DEL_MENU.http_build_query(['access_token'=>$corp_access_token,'agentid'=>$agentid]));
        return json_decode($ret, true);
    }
    
    //通讯录接口 - 管理成员
    const CREATE_USER = 'https://qyapi.weixin.qq.com/cgi-bin/user/create?';
    const UPDATE_USER = 'https://qyapi.weixin.qq.com/cgi-bin/user/update?';
    const DELETE_USER = 'https://qyapi.weixin.qq.com/cgi-bin/user/delete?';
    const BATCHDELETE_USER = 'https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?';
    const GET_USER = 'https://qyapi.weixin.qq.com/cgi-bin/user/get?';
    const GET_DEPT_USER = 'https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?';
    const GET_DEPT_USER_INFO = 'https://qyapi.weixin.qq.com/cgi-bin/user/list?';
    const INVITE_USER = 'https://qyapi.weixin.qq.com/cgi-bin/invite/send?';
    
    //通讯录接口 - 管理部门
    const CREATE_DEPT = 'https://qyapi.weixin.qq.com/cgi-bin/department/create?';
    const UPDATE_DEPT = 'https://qyapi.weixin.qq.com/cgi-bin/department/update?';
    const DELETE_DEPT = 'https://qyapi.weixin.qq.com/cgi-bin/department/delete?';
    const GET_DEPT = 'https://qyapi.weixin.qq.com/cgi-bin/department/list?';
    
     /*
      * 创建成员
        参数说明：
        access_token	调用接口凭证
        userid	成员UserID。对应管理端的帐号，企业内必须唯一。长度为1~64个字节
        name	成员名称。长度为1~64个字节
        department	成员所属部门id列表。注意，每个部门的直属成员上限为1000个
        position	职位信息。长度为0~64个字节
        mobile	手机号码。企业内必须唯一，mobile/weixinid/email三者不能同时为空
        gender	性别。1表示男性，2表示女性
        email	邮箱。长度为0~64个字节。企业内必须唯一
        weixinid	微信号。企业内必须唯一。（注意：是微信号，不是微信的名字）
        avatar_mediaid	成员头像的mediaid，通过多媒体接口上传图片获得的mediaid
        extattr	扩展属性。扩展属性需要在WEB管理端创建后才生效，否则忽略未知属性的赋值
     */
    static function createUser($corp_access_token,$userinfo)
    {
        $curl = new Curl();
        $ret = $curl->post(self::CREATE_USER.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($userinfo));
        return json_decode($ret, true);
    }
    
    /*
     * 更新成员
        参数说明：
        access_token	调用接口凭证
        userid	成员UserID。对应管理端的帐号，企业内必须唯一。长度为1~64个字节
        name	成员名称。长度为1~64个字节
        department	成员所属部门id列表。注意，每个部门的直属成员上限为1000个
        position	职位信息。长度为0~64个字节
        mobile	手机号码。企业内必须唯一，mobile/weixinid/email三者不能同时为空
        gender	性别。1表示男性，2表示女性
        email	邮箱。长度为0~64个字节。企业内必须唯一
        weixinid	微信号。企业内必须唯一。（注意：是微信号，不是微信的名字）
        avatar_mediaid	成员头像的mediaid，通过多媒体接口上传图片获得的mediaid
        extattr	扩展属性。扩展属性需要在WEB管理端创建后才生效，否则忽略未知属性的赋值
     */
    static function updateUser($corp_access_token,$userinfo)
    {
        $curl = new Curl();
        $ret = $curl->post(self::UPDATE_USER.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($userinfo));
        return json_decode($ret, true);
    }
    
    /*
     * 删除成员
        参数说明
        access_token	调用接口凭证
        userid	成员UserID。对应管理端的帐号
     */
    static function deleteUser($corp_access_token,$userid)
    {
        $curl = new Curl();
        $ret = $curl->get(self::DELETE_USER.http_build_query(['access_token'=>$corp_access_token,'userid'=>$userid]));
        return json_decode($ret, true);
    }
    
    /*
     * 批量删除成员
        参数说明
        access_token	调用接口凭证
        userid	成员UserID。对应管理端的帐号
     */
    static function batchdeleteUser($corp_access_token,$userlist)
    {
        $curl = new Curl();
        $ret = $curl->post(self::BATCHDELETE_USER.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($userlist));
        return json_decode($ret, true);
    }
    
    /*
     * 批量删除成员
        参数说明
        access_token	调用接口凭证
        userid	成员UserID。对应管理端的帐号
     */
//    static function batchdeleteUser($corp_access_token,$userlist)
//    {
//        $curl = new Curl();
//        $ret = $curl->post(self::BATCHDELETE_USER.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($userinfo));
//        return json_decode($ret, true);
//    }
    
    /*
     * 邀请成员关注
        参数说明
        access_token	调用接口凭证
        userid	成员UserID。对应管理端的帐号
     * 认证号优先使用微信推送邀请关注，如果没有weixinid字段则依次对手机号，邮箱绑定的微信进行推送，全部没有匹配则通过邮件邀请关注。 
     * 邮箱字段无效则邀请失败。 非认证号只通过邮件邀请关注。邮箱字段无效则邀请失败。 已关注以及被禁用成员不允许发起邀请关注请求。
     * 为避免骚扰成员，企业应遵守以下邀请规则：
     * 每月邀请的总人次不超过成员上限的2倍；每7天对同一个成员只能邀请一次。
     */
    static function inviteUser($corp_access_token,$userid)
    {
        $curl = new Curl();
        $ret = $curl->get(self::INVITE_USER.http_build_query(['access_token'=>$corp_access_token,'userid'=>$userid]));
        return json_decode($ret, true);
    }
    
    /*
     * 获取部门列表
        参数说明
        access_token	调用接口凭证
        id	部门ID
     */
    static function getDept($corp_access_token,$id=null)
    {
        $curl = new Curl();
        $ret = $curl->get(self::GET_DEPT.http_build_query(['access_token'=>$corp_access_token,'id'=>$id]));
        return json_decode($ret, true);
    }
    
    /*
        参数说明
        access_token	调用接口凭证
        department_id	获取的部门id
        fetch_child	1/0：是否递归获取子部门下面的成员
        status	0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加
     */
    static function getDeptuser($corp_access_token,$department_id,$fetch_child=1,$status=0)
    {
        $curl = new Curl();
        $ret = $curl->get(self::GET_DEPT_USER.http_build_query(['access_token'=>$corp_access_token,'department_id'=>$department_id,'fetch_child'=>$fetch_child,'status'=>$status]));
        return json_decode($ret, true);
    }
    
    /*
        参数说明
        access_token	调用接口凭证
        department_id	获取的部门id
        fetch_child	1/0：是否递归获取子部门下面的成员
        status	0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加
     */
    static function getDeptuserinfo($corp_access_token,$department_id,$fetch_child=1,$status=0)
    {
        $curl = new Curl();
        $ret = $curl->get(self::GET_DEPT_USER_INFO.http_build_query(['access_token'=>$corp_access_token,'department_id'=>$department_id,'fetch_child'=>$fetch_child,'status'=>$status]));
        return json_decode($ret, true);
    }
    
    /*
        参数说明
        access_token	调用接口凭证
        name	部门名称。长度限制为1~64个字节，字符不能包括\:*?"<>｜
        parentid	父亲部门id。根部门id为1
        order	在父部门中的次序值。order值小的排序靠前。
        id	部门id，整型。指定时必须大于1，不指定时则自动生成
     */
    static function createDept($corp_access_token,$deptinfo)
    {
        $curl = new Curl();
        $ret = $curl->post(self::CREATE_DEPT.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($deptinfo));
        return json_decode($ret, true);
    }
    
    /**
     * 
        参数说明
        access_token	调用接口凭证
        id	部门id
        name	更新的部门名称。长度限制为1~64个字节，字符不能包括\:*?"<>｜。修改部门名称时指定该参数
        parentid	父亲部门id。根部门id为1
        order	在父部门中的次序值。order值小的排序靠前。
     */
    static function updateDept($corp_access_token,$deptinfo)
    {
        $curl = new Curl();
        $ret = $curl->post(self::UPDATE_DEPT.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($deptinfo));
        return json_decode($ret, true);
    }
    
    /*
        参数说明
        access_token	调用接口凭证
        id	部门id。（注：不能删除根部门；不能删除含有子部门、成员的部门）
    */
    static function deleteDept($corp_access_token,$deptid)
    {
        $curl = new Curl();
        $ret = $curl->get(self::DELETE_DEPT.http_build_query(['access_token'=>$corp_access_token,'id'=>$deptid]));
        return json_decode($ret, true);
    }
    
    //IM接口 - 会话管理
    const CREATE_CHAT = 'https://qyapi.weixin.qq.com/cgi-bin/chat/create?';
    const UPDATE_CHAT = 'https://qyapi.weixin.qq.com/cgi-bin/chat/update?';
    const QUIT_CHAT = 'https://qyapi.weixin.qq.com/cgi-bin/chat/quit?';
    const GET_CHAT = 'https://qyapi.weixin.qq.com/cgi-bin/chat/get?';
    
    /**
     * 创建回话
     * @param type $corp_access_token   调用接口凭证
     * @param type $chatinfo    会话详情<json>
    参数说明
    chatid	会话id。字符串类型，最长32个字符。只允许字符0-9及字母a-zA-Z, 如果值内容为64bit无符号整型：要求值范围在[1, 2^63)之间，[2^63, 2^64)为系统分配会话id区间
    name	会话标题
    owner	管理员userid，必须是该会话userlist的成员之一
    userlist	会话成员列表，成员用userid来标识。会话成员必须在3人或以上，1000人以下
     * @return type
     */
    static function createChat($corp_access_token,$chatinfo)
    {
        $curl = new Curl();
        $ret = $curl->post(self::CREATE_CHAT.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($chatinfo));
        return json_decode($ret, true);
    }
    
    /**
     * 修改回话
     * @param type $corp_access_token   调用接口凭证
     * @param type $chatinfo    会话详情<json>
    参数说明
    chatid	会话id
    op_user	操作人userid
    name	会话标题
    owner	管理员userid，必须是该会话userlist的成员之一
    add_user_list	会话新增成员列表，成员用userid来标识
    del_user_list	会话退出成员列表，成员用userid来标识
     * @return type
     */
    static function updateChat($corp_access_token,$chatinfo)
    {
        $curl = new Curl();
        $ret = $curl->post(self::UPDATE_CHAT.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($chatinfo));
        return json_decode($ret, true);
    }
    
    /**
     * 退出回话
     * @param type $corp_access_token   调用接口凭证
     * @param type $chatinfo    会话详情<json>
    参数说明
    chatid	会话id
    op_user	操作人userid
     * @return type
     */
    static function quitChat($corp_access_token,$chatinfo)
    {
        $curl = new Curl();
        $ret = $curl->post(self::QUIT_CHAT.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($chatinfo));
        return json_decode($ret, true);
    }
    
    /**
     * 获取回话
     * @param type $corp_access_token   调用接口凭证
     * @param type $chatinfo    会话详情<json>
    参数说明
    chatid	会话id
     * @return type
     */
    static function getChat($corp_access_token,$chatinfo)
    {
        $curl = new Curl();
        $ret = $curl->post(self::GET_CHAT.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($chatinfo));
        return json_decode($ret, true);
    }
    
    //IM接口 - 会话管理
    const SEND_MSG = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?';
    
    static function sendMsg($corp_access_token,$msg)
    {
        $curl = new Curl();
        $ret = $curl->post(self::SEND_MSG.http_build_query(['access_token'=>$corp_access_token]), String::jsonEncode($msg));
        return json_decode($ret, true);
    }
    
    //企业号登录接口
    //https://qy.weixin.qq.com/cgi-bin/loginpage?corp_id=xxxx&redirect_uri=xxxxx&state=xxxx 
    const USER_LOGIN_PAGE = 'https://qy.weixin.qq.com/cgi-bin/loginpage';
    const GET_PROVIDER_TOKEN = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_provider_token';
    const GET_LOGIN_INFO = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_login_info?';
    /**
     * 获取套件授权页面地址
     * @param $suite_id
     * @param $pre_auth_code
     * @param $redirect_uri
     * @param $state
     * @return string
     */
    static function getCorpAuthPage($corp_id, $redirect_uri, $state) {
        $query = ['corp_id'=>$corp_id,'redirect_uri'=>$redirect_uri,'state'=>$state];
        return self::USER_LOGIN_PAGE.'?'.http_build_query($query);
    }
    
    /**
     * 获取应用提供商凭证
     * @param type 企业号（提供商）的登录信息
     * @return type
     * 参数说明
     * corpid   企业号（提供商）的corpid
     * provider_secret  提供商的secret，在提供商管理页面可见
     */
    static function getProviderToken($corplogin)
    {
        $curl = new Curl();
        $ret = $curl->post(self::GET_PROVIDER_TOKEN, String::jsonEncode($corplogin));
        return json_decode($ret, true);
    }
    
    /**
     * 获取企业号管理员登录信息
     * @param type $provider_access_token   服务提供商的accesstoken
     * @param type $auth_code   oauth2.0授权企业号管理员登录产生的code
     * @return type
     */
    static function getQyLoginInfo($provider_access_token,$auth_code)
    {
        $curl = new Curl();
        $ret = $curl->post(self::GET_LOGIN_INFO.http_build_query(['provider_access_token'=>$provider_access_token]), String::jsonEncode($auth_code));
        return json_decode($ret, true);
    }
    
    //https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=ACCESS_TOKE
    const GET_JSAPI_TICKET = 'https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?';
    
    /**
     * 获取JSSDK的config信息
     * @param type $access_token
     * @return type
     */
    static function getJsApiTicket($access_token)
    {
        $curl = new Curl();
        $ret = $curl->get(self::GET_JSAPI_TICKET.http_build_query(['access_token'=>$access_token]));
        return json_decode($ret, true);
    }
    
    const MEDIA_UPLOAD = 'https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE';
    static function uploadMedia($access_token, $type, $filepath) {
        $curl = new Curl();
        $ret = $curl->post(str_replace('TYPE', $type, str_replace('ACCESS_TOKEN', $access_token, self::MEDIA_UPLOAD)), ['media'=>'@'.$filepath]);
        return json_decode($ret, true);
    }
    
    const SYNC_USER = 'https://qyapi.weixin.qq.com/cgi-bin/batch/syncuser?access_token=ACCESS_TOKEN';
    static function syncUser($access_token, $media_id,$url='',$token='',$aeskey='') {
        $curl = new Curl();
        if(!empty($url) && !empty($token) && !empty($aeskey)) {
            //此分支是按照协议来的，但是目前为止，没有调通。 by fangl
            $json = json_encode([ 'media_id'=>$media_id,'callback'=>['url'=>$url,'token'=>$token,'encodingaeskey'=>base64_encode($aeskey)] ]);
        }
        else $json = json_encode([ 'media_id'=>$media_id]);
        \Yii::info($json,__METHOD__);
        $ret = $curl->post(str_replace('ACCESS_TOKEN', $access_token, self::SYNC_USER), $json);
        return json_decode($ret, true);
    }
}
