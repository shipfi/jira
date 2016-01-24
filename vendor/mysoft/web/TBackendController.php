<?php
/**
 * 多租户管理后台继承
 */
namespace mysoft\web;

class TBackendController extends Controller
{ 
	public function beforeAction($action) {
        $user_code = \yii::$app->session->get('user_code');
        if (!isset($user_code)) {
            $this->redirect(['tenant/login/index']);
        }
        return true;
    }
}
