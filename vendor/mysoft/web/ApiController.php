<?php

/*
 * support rest api authorize 
 * 
 * 
 */

namespace mysoft\web;
/**
 * Base For all Api service
 *
 * @author yangzhen
 */
class ApiController extends \yii\rest\Controller
{
    public $enableCsrfValidation = false;
    
    protected $orgcode;
    protected $format;
    public function init()
    {
        $this->orgcode = I("__orgcode");
        $this->format = I("__format","json");
        //鉴权
        if (YII_ENV != "dev") {
            $this->_check_auth_access(); 
        }

    }

    public function error($msg)
    {
        $response = \Yii::$app->response;
        $response->format =  $this->format=="json"? $response::FORMAT_JSON : $response::FORMAT_XML;
        $response->data = [
            "success"=>0,
            "data"=>$msg
        ];
        return $response;
    }

    public function response($msg)
    {
        $response = \Yii::$app->response;
        $response->format =  $this->format=="json"? $response::FORMAT_JSON : $response::FORMAT_XML;
        $response->data =
            [
                "success"=>1,
                "data"=>$msg
            ];
        return $response;
    }
    
    /**
      * 自定义输出
      *
      * @param 数据对象
      * @author 骆兵
    */
    public function  responseJson($data)
    {
         $response = \Yii::$app->response;
        $response->format =  $this->format=="json"? $response::FORMAT_JSON : $response::FORMAT_XML;
        $response->data =$data;
        return $response;
        
    }

    /**
     * 访问权限认证
     */
    protected function _check_auth_access()
    {
        try{
            
            $server = new \mysoft\sign\Server();
            $server->verify();
            
        }  catch (\Exception $ex){
            
            $message = $ex->getMessage();
            
            if(!in_array($ex->getCode(),[0,1,2,3])){
                $message = '未定义的授权异常:'.$message;
            }
            
            $this->error($message);
            \Yii::$app->end();
            
        }
        
        
    }
    
}
