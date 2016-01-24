<?php
namespace mysoft\user\micro;

/**
 * 微信认证类
 * @author fangl
 *
 */
class WxAuth extends Auth {
    
    public $client;
    
    protected function userInfoByField() {
        return 'p_user.wx_user_id';
    }
    
    protected function getAuthAccountId() {
        $client = $this->client?$this->client:new \mysoft\sign\Client();
        $wx_crop = json_decode($client->get("/api/wx-api/get-corp-info", ['__orgcode' => $this->orgcode]), true);
        if (!$wx_crop["success"]) {
            if (empty($wx_crop)) {
                $msg = '中台接口请求错误，请检查api_site配置';
            } else $msg = $wx_crop["data"];
                
            throw new AuthException($msg);
        }
            
        return \mysoft\user\WxAccount::getAccount($this->orgcode, $wx_crop["data"]["corpid"]);      
    }
}