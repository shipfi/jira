<?php
namespace mysoft\user\micro;

/**
 * 第三方应用认证类
 * @author fangl
 *
 */
class ThirdAuth extends Auth {
    
    public $params;
    public $secret;
    protected function userInfoByField() {
        return 'erp_user.user_code';
    }
    
    protected function getAuthAccountId() {
        $params = $this->params !== null?$this->params:\mysoft\pubservice\BasicParams::get($this->orgcode, 'third_app_user_code_params');
        $secret = $this->secret !== null?$this->secret:\mysoft\pubservice\BasicParams::get($this->orgcode, 'third_app_auth_secret');
        if(empty($params)) {
            throw new AuthException("未设置third_app_user_code_params参数，无法被第三方应用集成");
        }
        else $usercode = I($params);
        
        if(empty($user_code)) {
            $user_code = cookie('user_code@'.$this->orgcode);
        }
        else {
            if(!empty($secret)) {
                $user_code = \mysoft\helpers\AesHelper::decrypt($user_code,$secret);
            }
			
			cookie('user_code@'.$this->orgcode,$user_code,time()+30*24*60*60);
		}
        
        if(empty($usercode)) {
            throw new AuthException("无法从参数{$params}中获取用户code");
        }
        else return $usercode;
    }
}