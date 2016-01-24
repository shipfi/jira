<?php
/**
 * Created by PhpStorm.
 * User: zhangl
 * Date: 2016/1/21
 * Time: 20:51
 */
namespace home\controllers;

use yii\web\Controller;

class TestController extends Controller
{

    public function actionCurl()
    {
        $url = 'http://pd.mysoft.net.cn/AjaxRequirement/GetAllRequirementList.cspx';
        $curl = curl_init($url);
        $header = [
            'Authorization:Basic' . base64_encode('zhangl04:12345678'),
            'Content-Type:application/text'
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_USERPWD, "zhangl04:12345678");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($curl, CURLOPT_UNRESTRICTED_AUTH, 1);
        //curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        //curl_setopt($curl, CURLOPT_VERBOSE, true);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        print_r($info);
    }
}
