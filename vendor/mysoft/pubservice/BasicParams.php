<?php
namespace mysoft\pubservice;

class BasicParams {
    
    const GET_FROM_DB_WAIT_TIIME = 600; //从数据库取出的间隔时间为600秒，避免重复从数据库取数
    
    /**
     * [
     *  'orgcode1'=> [
     *      'params'=> [
     *          'param_code1'=> [
     *              'value'=>xxxx
     *          ]
     *      ],
     *      'lastgetfromdbtime'=>time()
     *   ]
     * ]
     * @var array
     */
    static $params = [];
    
    /**
     * 获取某租户id的基础参数
     * @param string $orgcode
     * @param string $param_code
     * @return NULL
     */
    static function get($orgcode, $param_code) {
        //首先查看内存是否命中，内存未命中取缓存
        if(!isset(self::$params[$orgcode]['params'][$param_code]['value'])) {
            self::$params[$orgcode] = self::_getFromCache($orgcode);
        }

        //其次查看缓存是否命中，缓存未命中取数据库
        if(!isset(self::$params[$orgcode]['params'][$param_code]['value'])) {
            if(isset(self::$params[$orgcode]) && self::$params[$orgcode]['lastgetfromdbtime'] < time() - self::GET_FROM_DB_WAIT_TIIME) {
                self::$params[$orgcode] = self::_getFromDb($orgcode);
            }
        }
        
        if(isset(self::$params[$orgcode]['params'][$param_code]['value'])) {
            return self::$params[$orgcode]['params'][$param_code]['value'];
        }
        else return null;
    }
    
    static function set() {
        throw E('不支持set方法，请参考中台参数设置进行基础参数set操作');
    }
    
    static function _getFromCache($orgcode) {
        $key = ['basic_params_{orgcode}',$orgcode];
        $cached = \Yii::$app->cache->get($key);
        return $cached;
    }
    
    static function _setToCache($params,$orgcode) {
        $key = ['basic_params_{orgcode}',$orgcode];
        return \Yii::$app->cache->set($key, $params);
    }
    
    /**
     * 从数据库中取出基础参数列表，并自动缓存。请在更新参数之后调用此方法刷新缓存
     * @param string $orgcode
     * @return array
     */
    static function _getFromDb($orgcode) {
        $sql = 'select param_code,param_value from p_basic_params';
        $ret = DB($orgcode)->createCommand($sql)->queryAll();
        $params = ['params'=>[],'lastgetfromdbtime'=>time()];
        foreach($ret as $r) {
            //万一将来需要取params_type之类的值时，扩展这里等号右边的key
            $params['params'][$r['param_code']] = ['value'=>$r['param_value']];
        }
        //数据库内容缓存到内存中
        self::_setToCache($params, $orgcode);
        return $params;
    }
    
    static function _clear($orgcode) {
        
    }
}