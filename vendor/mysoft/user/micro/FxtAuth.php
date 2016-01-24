<?php
namespace mysoft\user\micro;

/**
 * 复兴通来源用户鉴权
 * 
 * __from=fxt&authcode=xxx
 * 
 * authcode由“第三方应用鉴权参数”配置项确定
 * 
 * @author fangl
 *
 */
class FxtAuth extends Auth {
    const FXT_EXPIRE = 300;
    public $params;
    
    protected function userInfoByField() {
        return 'erp_user.user_code';
    }
    
    protected function getAuthAccountId() {
        $params = $this->params?$this->params:\mysoft\pubservice\BasicParams::get($this->orgcode, 'third_app_user_code_params');
        if(empty($params)) {
            throw new AuthException("未设置第三方应用集成标识，无法被第三方应用集成");
        }
        else $authcode = I($params);

        if(empty($authcode)) {
            $usercode = cookie('user_code@'.$this->orgcode);
            if(!empty($usercode)) {
                return $usercode;
            }
            else throw new AuthException('authcode不存在');
        }
        else {
            $authcode = \mysoft\helpers\AesHelper::decrypt($authcode);
            $authcode = json_decode($authcode,true);
            if(!empty($authcode) && isset($authcode['user_code']) && isset($authcode['timestamp']) && isset($authcode['orgcode'])) {
                
                if(time() - $authcode['timestamp'] > self::FXT_EXPIRE) {
                    throw new AuthException('authcode已经过期');
                }
                
                if($authcode['orgcode'] !== $this->orgcode) {
                    throw new AuthException('租户ID不匹配');
                }
                
                cookie('user_code@'.$this->orgcode,$authcode['user_code'],time()+24*60*60); //复兴通的cookie只存一天
                
                if(YII_ENV != 'unittest') {
                    $query = \Yii::$app->request->getQueryParams();
                    if(isset($query[$params])) {
                        unset($query[$params]);
                    }
                    \Yii::$app->response
                        ->redirect(\Yii::$app->request->getHostInfo().\Yii::$app->params['static_host'].'/'.\Yii::$app->request->getPathInfo()."?".http_build_query($query))
                        ->send();
                    //return false;
                }
                return $authcode['user_code'];
            }
            else throw new AuthException('authcode解析失败');
        }
    }
}