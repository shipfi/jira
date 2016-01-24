<?php
namespace mysoft\helpers;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use Yii;
use mysoft\helpers\Url;

/**
 * @see yii\web\view & mysoft\helpers\assert & functions.php#_assert
 * @author fangl
 *
 */
class AssetHelper {
    
    /**
     * Registers a CSS code block.
     * @param string $cssFile the CSS file to be registered. 
     * use @webroot instead of @web if you put a file in webroot/ path
     * @param array $options the HTML attributes for the `<style>`-tag.
     * @param string $key the key that identifies the CSS code block. If null, it will use
     * $css as the key. If two CSS code blocks are registered with the same key, the latter
     * will overwrite the former.
     */
    static function registerCss($cssFile, $options = [], $key = null)
    {
        $view = \Yii::$app->getView();
        $key = $key ?: md5($cssFile);
        $cssFile = Yii::getAlias($cssFile);
        $css = file_get_contents($cssFile);
        $view->css[$key] = Html::style($css, $options);
    }
    
    /**
     * Registers a CSS file.
     * @param string $url the CSS file to be registered.
     * @param array $options the HTML attributes for the link tag. Please refer to [[Html::cssFile()]] for
     * the supported options. The following options are specially handled and are not treated as HTML attributes:
     *
     * - `depends`: array, specifies the names of the asset bundles that this CSS file depends on.
     *
     * @param string $key the key that identifies the CSS script file. If null, it will use
     * $url as the key. If two CSS files are registered with the same key, the latter
     * will overwrite the former.
     */
    static function registerCssFile($url, $options = [], $key = null)
    {
        $view = \Yii::$app->getView();
        $filePath = \Yii::getAlias(str_replace('@web', '@webroot', $url));
        if(file_exists($filePath)) {
            $timestamp = filemtime($filePath);
        }
        else {
            $timestamp = '';
        }
        $url = Url::to($url);
        $key = $key ?: $url;
        
        if(empty($timestamp)) {
            if(YII_ENV == 'dev') {
                $timestamp = date("Ymdh",time());
            }
            else {
                $timestamp = \Yii::$app->cache->get(['static_cache_{url}',md5($url)]);
                if(empty($timestamp) && strpos($url, 'static') && strpos($url, 'http') === 0) {
                    $curl = new \mysoft\http\Curl();
                    $curl->head($url);
                    $lastModify = $curl->getHeader('Last-Modified');
                    $timestamp = strtotime($lastModify);
                    \Yii::$app->cache->set(['static_cache_{url}',md5($url)],$timestamp);
                }
            }
        }
        
        $position = ArrayHelper::remove($options, 'position', true);
        if(!$position) {
            return Html::cssFile($url.(empty($timestamp)?'':(strstr($url,'?')?'&':'?').'v='.$timestamp), $options)."\n";
        }
        else $view->cssFiles[$key] = Html::cssFile($url.(empty($timestamp)?'':(strstr($url,'?')?'&':'?').'v='.$timestamp), $options);
    }
    
    /**
     * Registers a JS code block.
     * @param string $jsFile the JSFil to be registered
     * use @webroot instead of @web if you put a file in webroot/ path
     * @param integer $position the position at which the JS script tag should be inserted
     * in a page. The possible values are:
     *
     * - [[View::POS_HEAD]]: in the head section
     * - [[View::POS_BEGIN]]: at the beginning of the body section
     * - [[View::POS_END]]: at the end of the body section
     * - [[View::POS_LOAD]]: enclosed within jQuery(window).load().
     *   Note that by using this position, the method will automatically register the jQuery js file.
     * - [[View::POS_READY]]: enclosed within jQuery(document).ready(). This is the default value.
     *   Note that by using this position, the method will automatically register the jQuery js file.
     *
     * @param string $key the key that identifies the JS code block. If null, it will use
     * $js as the key. If two JS code blocks are registered with the same key, the latter
     * will overwrite the former.
     */
    static function registerJs($jsFile, $position=View::POS_END, $key = null)
    {
        $view = \Yii::$app->getView();
        $key = $key ?: md5($jsFile);
        $jsFile = \Yii::getAlias($jsFile);
        $js = file_get_contents($jsFile);
        $view->js[$position][$key] = $js;
        if($position == View::POS_LOAD || $position == View::POS_READY) {
            self::registerJsFile('@web/js/jquery.min.js', ['position'=>View::POS_HEAD]);
        }
    }
    
    /**
     * Registers a JS file.
     * @param string $url the JS file to be registered.
     * @param array $options the HTML attributes for the script tag. The following options are specially handled
     * and are not treated as HTML attributes:
     *
     * - `depends`: array, specifies the names of the asset bundles that this JS file depends on.
     * - `position`: specifies where the JS script tag should be inserted in a page. The possible values are:
     *     * [[POS_HEAD]]: in the head section
     *     * [[POS_BEGIN]]: at the beginning of the body section
     *     * [[POS_END]]: at the end of the body section. This is the default value.
     *     * false while not register but return
     *
     * Please refer to [[Html::jsFile()]] for other supported options.
     *
     * @param string $key the key that identifies the JS script file. If null, it will use
     * $url as the key. If two JS files are registered with the same key, the latter
     * will overwrite the former.
     */
    static function registerJsFile($url, $options = [], $key = null)
    {
        $view = \Yii::$app->getView();
        $filePath = \Yii::getAlias(str_replace('@web', '@webroot', $url));
        if(file_exists($filePath)) {
            $timestamp = filemtime($filePath);
        }
        else {
            $timestamp = '';
        }
        $url = Url::to($url);
        $key = $key ?: $url;
        
        if(empty($timestamp)) {
            if(YII_ENV == 'dev') {
                $timestamp = date("Ymdh",time());
            }
            else {
                $timestamp = \Yii::$app->cache->get(['static_cache_{url}',md5($url)]);
                if(empty($timestamp) && strpos($url, 'static') && strpos($url, 'http') === 0) {
                    $curl = new \mysoft\http\Curl();
                    $curl->head($url);
                    $lastModify = $curl->getHeader('Last-Modified');
                    $timestamp = strtotime($lastModify);
                    \Yii::$app->cache->set(['static_cache_{url}',md5($url)],$timestamp);
                }
            }
        }
        
        $position = ArrayHelper::remove($options, 'position', View::POS_END);
        if(!$position) {
            return Html::jsFile($url.(empty($timestamp)?'':(strstr($url,'?')?'&':'?').'v='.$timestamp), $options)."\n";
        }
        else $view->jsFiles[$position][$key] = Html::jsFile($url.(empty($timestamp)?'':(strstr($url,'?')?'&':'?').'v='.$timestamp), $options);
    }
}