<?php
namespace home\controllers;

use Yii;
use mysoft\web\Controller;
use mysoft\http\Curl;


class IndexController extends Controller
{
    public $apiHost = "";

    public function actions()
    {
        $this->layout = false;
        $this->apiHost = \Yii::$app->params['jiraHost'];
    }

    //登录
    public $WEB_API_LOGIN = '{host}/rest/auth/latest/session?os_username={name}&os_password={pwd}';

    public function actionIndex()
    {
        if (cookie('jira_u')) {
            return $this->redirect(U(['issue/index', 'pk' => cookie('jira_pk')]));
        } else {
            return $this->render('index');
        }
    }

    public function actionLogout()
    {
        cookie('jira_u', null);
        cookie('jira_p', null);
        cookie('jira_pk', null);
        \Yii::$app->session->set('name', null);
        \Yii::$app->session->set('psw', null);
        return $this->redirect(U(['index']));
    }

    public function actionAjaxLogin()
    {
        $name = I('name');
        $pwd = I('psw');
        $pk = I('projkey');
        $isRem = I('rem');
        $curl = new Curl();
        $url = str_replace(array('{host}', '{name}', '{pwd}'), array($this->apiHost, $name, $pwd), $this->WEB_API_LOGIN);
        $rs = $curl->get($url);
        $rs = json_decode($rs, true);
        //注释：这个$rs返回是的当前用户登录日志，如：上一次登录时间
        if (!empty($rs)) {
            if (!empty($rs['errorMessages'])) {
                //echo '登录失败，请重试';
                return $this->ajax_response("0", '账号或密码错误，请重试');
            } else {
                if ($isRem == "1") {
                    cookie('jira_u', $name);
                    cookie('jira_p', md5($pwd));
                }
                cookie('jira_pk', $pk);
                \Yii::$app->session->set('name', $name);
                \Yii::$app->session->set('psw', $pwd);
                $rd_url = U(['issue/index', 'pk' => $pk]);
                return $this->ajax_response("1", $rd_url);
            }
        }
    }


}
