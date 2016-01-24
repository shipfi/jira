<?php
namespace mysoft\user\micro;

/**
 * 演示中心认证类
 * @author fangl
 *
 */
class DemoAuth extends Auth {
    
    protected function userInfoByField() {
        return 'erp_user.user_code';
    }
    
    protected function getAuthAccountId() {
        if($this->orgcode != 'my560b3ffceb31b') {
            throw new AuthException('只允许体验租户进入体验中心');
        }
        else {
            $user_code = I('user_code');
            if(empty($user_code)) {
                $user_code = cookie('user_code@'.$this->orgcode);
            }
            else cookie('user_code@'.$this->orgcode,$user_code,time()+30*24*60*60);
            if(empty($user_code)) {
                throw new AuthException('缺少参数user_code');
            }
            else return $user_code;
        }
    }
}