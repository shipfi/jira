<?php
namespace mysoft\user\micro;

class LandrayAuth extends Auth {
    
    const LTPATOKEN = 'LtpaToken';
    
    public $secret;
    
    protected function userInfoByField() {
        return 'erp_user.user_code';
    }
    
    protected function getAuthAccountId() {
        $cookie = $_COOKIE['LtpaToken'];
        $cookie = str_replace(' ', '+', $cookie);
        $secret = $this->secret?$this->secret:\mysoft\pubservice\BasicParams::get($this->orgcode, 'landray_secret');
        
        if(empty($secret)) {
            throw new AuthException('未设置蓝凌密钥，无法支持蓝凌oa集成');
        }
        
        $usercode = \mysoft\third\landray\Helper::decode_sso($cookie, $secret);
        
        if(empty($usercode)) {
            throw new AuthException("解析username失败:".$cookie);
        }
        else return $usercode;
    }
}