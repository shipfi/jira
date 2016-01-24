<?php
namespace yii\debug;

use yii\data\Pagination;

class MyPagination extends Pagination {
    
    public function createUrl($page, $pageSize = null, $absolute = false) {
        $url = parent::createUrl($page, $pageSize, $absolute);
        if(!$absolute) {
            return \Yii::$app->params['static_host'].'/'.ltrim($url,'/');
        }
        else return $url;
    }
}