<?php
namespace home\controllers;

use Yii;

class IssueController extends BaseController
{

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * ajax-get-issues
     */
    public function actionAjaxGetIssues()
    {
        $projKey = cookie('jira_pk');
        $param = [
            'name' => $this->name,
            'search' => [
                'assignee' => $this->name,
                'projKey' => $projKey,
                'startAt' => empty(I('start')) ? 0 : I('start')
            ]
        ];
        $rs = $this->issueSrvs->getIssue($param['search']);
        foreach ($rs['issues'] as $k => &$v) {
            $v['detail_url'] = U(['show', 'issueKey' => $v['key']]);
        }
        $this->ajax_response('1', '', $rs);
    }

    public function actionShow()
    {
        $rs = $this->issueSrvs->getIssueByKey('YDSP-543');
        if (!empty($rs)) {
            $fields = $rs->result['fields'];
            $data = [
                'summary' => $fields['summary'],
                'detail' => [
                    'issuetype' => [
                        'name' => $fields['issuetype']['name'],
                        'iconUrl' => $fields['issuetype']['iconUrl']
                    ],
                    'status' => [
                        'name' => $fields['status']['name'],
                        'description' => $fields['status']['description'],
                        'iconUrl' => $fields['status']['iconUrl'],
                    ],
                    'priority' => [
                        'name' => $fields['priority']['name'],
                        'iconUrl' => $fields['priority']['iconUrl'],
                    ],
                    'labels' => $fields['labels'],
                    'fixVersions' => $fields['fixVersions'][0]['name']
                ],
                'assignee' => $fields['assignee'],
                'reporter' => $fields['reporter'],
                'comment' => $fields['comment'],
                'attachment' => $fields['attachment']
            ];
        }
        return $this->render('show', ['tpData' => $data]);
    }
}
