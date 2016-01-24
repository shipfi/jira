<?php
/**
 * Created by PhpStorm.
 * User: luob02
 * Date: 2015/11/6
 * Time: 16:31
 */
namespace console\controllers;
use yii\console\Controller;
use srvs\queue\NoticeQueue;

class NoticeQueueController extends Controller{

    public function actionPushNotice()
    {
        $queue = new NoticeQueue('pub_notice_queue');
        $queue->pushNotice();
    }

    /**
     * 初始化公告推送任务
     * 添加推送任务到队列
     * @author 骆兵
     */
    public function actionSendNotice($notice)
    {
        $srv = new \srvs\pubnotice\NoticeService('');
        $srv->sendNotice(json_decode($notice,true));
    }
}