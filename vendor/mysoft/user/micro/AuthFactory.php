<?php
namespace mysoft\user\micro;

/**
 * 轻应用鉴权工厂方法
 * @author fangl
 *
 */
class AuthFactory {
    
    const FROM_WX = 'wx'; //微信来源鉴权
    const FROM_WZS = 'wzs'; //微助手来源鉴权
    const FROM_WZS2 = 'wzs2';   //微助手2.0来源鉴权
    const FROM_THIRD = 'third'; //第三方应用
    const FROM_THIRD2 = 'third2'; //第三方应用2版协议
    
    const FROM_DEMO = 'demo'; //体验中心
    
    const FROM_FXT = 'fxt'; //复兴通
    
    const FROM_LANDRAY = 'landray'; //蓝凌oa
    
    static $auth = [
        self::FROM_WX => 'mysoft\user\micro\WxAuth',
        self::FROM_WZS => 'mysoft\user\micro\WzsAuth',
        self::FROM_WZS2 => 'mysoft\user\micro\Wzs2Auth',
        self::FROM_THIRD => 'mysoft\user\micro\ThirdAuth',
        self::FROM_THIRD2 => 'mysoft\user\micro\Third2Auth',
        self::FROM_DEMO => 'mysoft\user\micro\DemoAuth',
        self::FROM_FXT => 'mysoft\user\micro\FxtAuth',
        self::FROM_LANDRAY => 'mysoft\user\micro\LandrayAuth',
    ];
    
    /**
     * 
     * @param string $orgcode
     * @param string $from
     * @throws AuthException
     * @return Auth
     */
    static function getAuth($orgcode, $from) {
        if(empty($orgcode)) {
            throw new AuthException('不合法的租户ID参数');
        }
        elseif(array_key_exists($from , self::$auth)) {
            return new self::$auth[$from]($orgcode);
        }
        else throw new AuthException('不合法的from参数:'.$from);
    }

}