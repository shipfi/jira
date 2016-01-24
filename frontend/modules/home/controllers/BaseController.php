<?php
namespace home\controllers;

use srvs\jira\JiraIssueService;
use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
    public $issueSrvs;
    public $name;
    public $pwd;

    public function actions()
    {
        $this->layout = false;
        $this->name = \Yii::$app->session->get('name');
        $this->pwd = \Yii::$app->session->get('psw');
        $this->issueSrvs = new JiraIssueService($this->name, $this->pwd);
    }

    public function ajax_response($isSuccess = 1, $msg = "", $result = []) {
        $response = Yii::$app->response;
        $response->format = $response::FORMAT_JSON;
        $sub_ticket = '';
        if (isset($result['sub_ticket'])) {
            $sub_ticket = $result['sub_ticket'];
        }
        $result = ['isSuccess' => $isSuccess, 'message' => $msg, 'result' => $result, 'sub_ticket' => $sub_ticket];
        $response->data = $result;
        return $response;
    }
}
