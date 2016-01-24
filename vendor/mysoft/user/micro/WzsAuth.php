<?php
namespace mysoft\user\micro;

/**
 * 微助手3.0认证类
 * @author fangl
 *
 */
class WzsAuth extends Auth {
    
    
    protected function userInfoByField() {
        return 'p_user.openid';
    }
    
    protected function getAuthAccountId() {
        // @codeCoverageIgnoreStart
        //获取设备号
        $deviceId = isset(\Yii::$app->request->headers["deviceId"])?\Yii::$app->request->headers["deviceId"]:\Yii::$app->request->headers["deviceid"];
        if (empty($deviceId)) {
            //从头部取不到值时，从URL取
            $deviceId = I('deviceId');
            if(empty($deviceId)) {
                $deviceId = I('deviceid');
            }
        }
            
        if(empty($deviceId)) {
            $deviceId = cookie('deviceId');
        }
        else {
            cookie('deviceId', $deviceId, time() + 2 * 7 * 24 * 3600);
        }
            
        if (empty($deviceId)) {
            throw new AuthException("无法获取当前设备的DeviceId");
        }
            
        return \mysoft\user\WzsAccount::getAccount($this->orgcode, $deviceId);
        // @codeCoverageIgnoreEnd
    }
}