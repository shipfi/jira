<?php

namespace srvs\jira;

class JiraBaseService
{
    public $jiraApi;
    public $apiHost;
    public $pageCount = 10;
    public $pageTotal = 250;

    public function __construct($name, $password)
    {
        $this->apiHost = \Yii::$app->params['jiraHost'];
        $this->jiraApi = new \srvs\Jira\Api(
            $this->apiHost,
            new \srvs\Jira\Api\Authentication\Basic($name, $password)
        );
    }

    protected function getFields(){
        return 'id,key,summary';
    }
}
