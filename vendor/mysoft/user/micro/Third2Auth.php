<?php
namespace mysoft\user\micro;

/**
 * 第三方应用认证类2（加强版协议）
 * @author fangl
 *
 */
class Third2Auth extends Auth {
    
    public $params;
    
    public $curl;
    
    protected function userInfoByField() {
        return 'erp_user.user_code';
    }
    
    protected function getAuthAccountId() {
        $params = $this->params?$this->params:\mysoft\pubservice\BasicParams::get($this->orgcode, 'third_app_user_code_params');
        if(empty($params)) {
            throw new AuthException("未设置third_app_user_code_params参数，无法被第三方应用集成");
        }
        else $authcode = I($params);
        
        if(empty($authcode)) {
            $user_code = cookie('user_code@'.$this->orgcode);
            if(empty($user_code)) {
                throw new AuthException('authcode不存在');
            }
            else return $user_code;
        }
        else {
            $curl = $this->curl?$this->curl:new \mysoft\http\Curl();
            $url = $this->params?"":\mysoft\pubservice\BasicParams::get($this->orgcode, 'third_app_authcode_url');
            $ret = json_decode($curl->get($url.$authcode),true);
            if(!empty($ret) && isset($ret['errcode']) && $ret['errcode'] == 0 && isset($ret['user_code'])) {
                $user_code = $ret['user_code'];
                cookie('user_code@'.$this->orgcode,$user_code,time()+30*24*60*60);
                return $user_code;
            }
            else if(empty($ret)) {
                throw new AuthException('第三方接口未返回');
            }
            else {
                throw new AuthException('第三方接口返回：'.json_encode($ret));
            }
        }
    }
    
}