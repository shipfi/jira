<?php

namespace srvs\jira;

class JiraIssueService extends JiraBaseService
{
    public function __construct($name, $password)
    {
        parent::__construct($name, $password);
    }

    public function getIssue($search)
    {
        $jql = "project='" . $search['projKey'] . "' and assignee='" . $search['assignee'] . "' order by updated, priority desc";
        $list = $this->jiraApi->search($jql, $search['startAt'], $this->pageCount, $this->getFields());
        if (!empty($list) && isset($list->result)) {
            $issues = $list->result['issues'];
            foreach ($issues as $k => &$v) {
                unset($v['expand']);
                unset($v['self']);
            }
            return [
                'issues' => $issues,
                'count' => $this->pageCount,
                'start' => $search['startAt'],
                'total' => $this->pageTotal
            ];
        } else {
            return [
                'issues' => [],
                'count' => 0,
                'start' => 0,
                'total' => 0
            ];
        }
    }

    public function getIssueByKey($issueKey){
        $rs = $this->jiraApi->getIssue($issueKey);
        return $rs;
    }
}
