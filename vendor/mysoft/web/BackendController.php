<?php
/**
 * 扩展控制器，继承优化
 * @author yangz03
 * @since 2015-01-17
 */
namespace mysoft\web;

/**
 * 后端控制器
 * Class BackendController
 * @package mysoft\web
 */
class BackendController extends Controller
{
    public function init() {
        parent::init();
        //identityclass在controller里面显示的指定
        \Yii::$container->set('yii\web\User',['identityClass'=>'mysoft\user\AdminIdentity','enableAutoLogin'=>true]);
        \Yii::$app->user->identityClass = 'mysoft\user\AdminIdentity'; //针对autologin场景
    }
    function beforeAction($action)
    {
         
        if( \Yii::$app->user->isGuest || \Yii::$app->user->getIdentity()->unitname != I('__orgcode') ) {
            
            $this->goHome();
            return false;
        }
        else return true;
    }


}
