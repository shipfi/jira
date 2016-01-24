<?php
namespace mysoft\user;

use mysoft\pubservice\Conf;
/**
 * Class WxAccount 微信账号
 * @package mysoft\user
 */
class WxAccount implements IAccount {
    
    const AUTHORIZE = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=CORPID&redirect_uri=REDIRECT_URI&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
    
    /**
     * 获取账号信息，页面会跳转，要求之前不能有header输出。
     * 会使用configsetting::auth2_site字段以定位认证页面地址
     * @param string $tenantId
     * @param string $corpId
     * @return Ambigous <string, unknown, \yii\web\array, \yii\web\mixed>
     */
    public static function getAccount($tenantId,$corpId){
        //首先从session中取授权页面传过来的userwxid，存在则设置一下cookie
        $userid = \Yii::$app->session->get('__userwxid@'.$tenantId);
        if(!empty($userid)) {
            cookie('__userwxid@'.$tenantId, $userid, time()+30*24*60*60);
            return $userid;
        }
        else {
            //其次从cookie中取
            $userid = cookie('__userwxid@'.$tenantId);
            if(!empty($userid)) {
                return $userid;
            }
            else {
                //最后做跳转
                $redirect = \Yii::$app->request->getHostInfo() .\Yii::$app->params['static_host']. \Yii::$app->request->getUrl();;
                $redirect_uri = Conf::getConfig('api_site').'/api/qy-auth2/after-auth?params='.urlencode(base64_encode(json_encode(['corp_id'=>$corpId,'tenant_id'=>$tenantId,'redirect'=>$redirect])));
                $url = str_replace('STATE', uniqid(), str_replace('REDIRECT_URI', urlencode($redirect_uri), str_replace('CORPID', $corpId, self::AUTHORIZE)));
                \Yii::$app->response->redirect($url)->send();
            }
        }
    }
}