<?php
namespace mysoft\caching;

class MyFileCacheProxy extends \yii\caching\FileCache {
    
    public $useMemcached = false;

    public $persistentId;

    public $options;

    public $username;

    public $password;

    private $_cache = null;

    public $servers = [];
 
}