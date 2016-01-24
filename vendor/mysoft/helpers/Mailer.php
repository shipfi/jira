<?php
namespace mysoft\helpers;

class Mailer extends \yii\swiftmailer\Mailer {
    
    private $email_cfg;
    
    public function __construct($config=[]) {
        $this->email_cfg = \mysoft\pubservice\Conf::getConfig('site_email');
        $this->email_cfg = json_decode($this->email_cfg, true);
        $config = array_merge($this->email_cfg,$config);
        parent::__construct($config);
    }

    public function getEmailCfg() {
        return $this->email_cfg;
    }
}