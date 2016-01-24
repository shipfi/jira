<?php
namespace mysoft\user\micro;

/**
 * 微助手2.0认证类
 * @author fangl
 *
 */
class Wzs2Auth extends Auth {

    public $curl;
    public $wzs2_site;
    
    protected function userInfoByField() {
        return 'erp_user.user_code';
    }
    
    protected function getAuthAccountId() {
        $appId = I('appId');
        $entCode = I('entCode');
        $sign = I('sign');
        if( !empty($appId) && !empty($entCode) && !empty($sign) ) {
                
            $curl = $this->curl?$this->curl:new \mysoft\http\Curl();
            $wzs2_site = $this->wzs2_site?$this->wzs2_site:\mysoft\pubservice\Conf::getConfig('wzs2_api_site');
            $ret = $curl->get($wzs2_site.'/api/Enterprise/GetAppKey',['entcode'=>$entCode,'appId'=>$appId]);
            if(empty($ret)) {
                throw new AuthException('wzs2 get_app_key接口无法访问');
            }
            else $ret = json_decode($ret,true);
            
            //get_app_key返回为{'data':'xxx'}
            if(!isset($ret['data']) || empty($ret['data'])) {
                throw new AuthException('wzs2 get_app_key返回值为空');
            }
            else $key = $ret['data'];
            
            $userinfo = \mysoft\helpers\AesHelper::decrypt($sign,$key);
            $userinfo = json_decode($userinfo,true);
            if(empty($userinfo) || !isset($userinfo['userCode'])) {
                throw new AuthException('sign无法解密出usercode');
            }
            else if(!isset($userinfo['timeStamp']) || time()*1000 - $userinfo['timeStamp'] > 24*60*60 ) {
                throw new AuthException('时间戳已经过期');
            }
            else $usercode = $userinfo['userCode'];
            
            if(empty($user_code)) {
                $user_code = cookie('user_code@'.$this->orgcode);
            }
            else cookie('user_code@'.$this->orgcode,$user_code,time()+30*24*60*60);
            
            return $usercode;
        }
        else throw new AuthException('wzs2 验证方式缺乏必要的appId,entCode,sign参数');
    }
}