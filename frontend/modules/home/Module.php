<?php
 /**
  * Module.php
  *
  * @author        fangliang
  * @create_time	   2015-06-16
  */

namespace modules\home;


class Module extends \yii\base\Module
{
    public $layout = "main";
    public $controllerNamespace = 'home\controllers';

    public function init()
    {
        parent::init();
        //do something init here
        \Yii::setAlias("home", __DIR__);
    }
} 