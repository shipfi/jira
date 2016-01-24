<?php
namespace mysoft\helpers;

use yii\helpers\BaseUrl;

/**
 * 为了兼容nginx取内容的时候，因为是反向代理，导致无法获取alias。这里，增加一个static_host代表alias用于拼接。
 * @author fangl
 *
 */
class Url extends BaseUrl {
    
    /**
     * 默认加上\Yii::$app->params['site_uri']到url上面，
     * 为兼容cdn取内容和nginx反向代理资源文件无法引用
     * @param string $url
     * @return string
     */
    public static function to($url = '', $scheme = false)
    {
        $static_host = isset(\Yii::$app->params['static_host'])?\Yii::$app->params['static_host']:'';
        $url = parent::to($url, $scheme);
        if(strncmp($url, 'http', strlen('http')) == 0) {
            //如果带上了完整的scheme，则不拼接static_host
            return $url;
        }
        else return $static_host.$url;
    }
}