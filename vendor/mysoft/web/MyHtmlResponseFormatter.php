<?php

namespace mysoft\web;

use yii\web\HtmlResponseFormatter;

/**
 * 兼容前端_STATIC_，_WEB_语法替换。
 * 要求此Formatter必须注入到Response中，mysoft\web\Controller中自动往Yii::$app->response对象注入了此formatter。
 * 如果你自己不是用的Yii::$app->response，那么请在构造Response对象的时候设置$formatters[Response::FORMAT_HTML] = 'mysoft\web\MyHtmlResponseFormatter';
 * @author fangl
 *
 */
class MyHtmlResponseFormatter extends HtmlResponseFormatter {
    
    /**
     * 兼容前端_STATIC_，_WEB_语法替换
     * @param Response $response the response to be formatted.
     */
    public function format($response)
    {
//         if (stripos($this->contentType, 'charset') === false) {
//             $this->contentType .= '; charset=' . $response->charset;
//         }
//         $response->getHeaders()->set('Content-Type', $this->contentType);
//         if ($response->data !== null) {
//             $response->content = $response->data;
//         }
        parent::format($response);
        if(is_string($response->content)) {
            $static_host = isset(\Yii::$app->params['static_host'])?\Yii::$app->params['static_host']:'';
            //$response->content = str_replace('_STATIC_', \mysoft\pubservice\Conf::getConfig('static_site'), $response->content);
            $response->content = str_replace('_WEB_', $static_host.\Yii::getAlias('@web'), $response->content);
        }
    }
}