<?php
namespace mysoft\base;

/**
 * 将基镜有关的业务逻辑提取出来
 * @author fangl 2015.11.2
 */
trait DbFixture {
    
    protected $testDbHost;  //测试数据库服务器host
    protected $testDbPort; //测试数据库服务器port
    protected $testDbUser;   //测试用户名
    protected $testDbPasswd; //测试服务器用户密码
    protected $configDb; //测试配置库名
    //以上配置从默认配置文件中取
    
    protected $tenantDb = 'dev_test';   //测试租户库名，默认恒定为dev_test，此库不会默认创建
    
    protected $tenantFixtureDataFile; //测试库准备数据文件
    protected $configFixtureDataFile;   //配置库准备数据文件
    
    private $_fixtures; //已经加载的fixtures数据
    
    private $_db;
    
    protected function init() {
        //从启动配置文件中初始化配置
        $dsn = \Yii::$app->db->dsn;
        $dsn = ltrim($dsn,'mysql:');
        $dsn = explode(';',$dsn);
        $db = [];
        foreach ($dsn as $p) {
            $p = explode('=', $p);
            $db[$p[0]] = $p[1];
        }
        
        $this->testDbHost = $db['host'];
        $this->testDbPort = isset($db['port'])?$db['port']:'3306';
        $this->testDbUser = \Yii::$app->db->username;
        $this->testDbPasswd = \Yii::$app->db->password;
        $this->configDb = $db['dbname'];
        
        if($this->configDb != 'config_test') {
            throw new \Exception('config dbname is not config_test please check your config');
        }
    }
    
    protected function setUp() {
        $this->init();
        $this->dbUp();
    }
    
    protected function tearDown() {
        $this->dbDown();
    }
    
    /**
     * 获得当前调用的子类所在目录;
     * 如果这里返回的不正确，请在子类中重写此方法为 return __DIR__;
     * 基镜数据文件默认为子类的同级目录寻址。
     * @return string
     */
    protected function getDir() {
        $dir = str_replace(['tests/phpunit','tests\phpunit'], \Yii::getAlias('@tests/phpunit'), get_class($this));
        $dir = str_replace('\\','/',$dir);
        return dirname($dir);
    }
    
    /**
     * 初始化基镜数据库,会在setUp时自动调用
     * @throws \Exception
     */
    protected function dbUp() {
        $this->loadFixture(__DIR__.DIRECTORY_SEPARATOR.'_configDb.php', $this->configDb);
            
        if(!empty($this->configFixtureDataFile)) {
            if(!file_exists($this->getDir().DIRECTORY_SEPARATOR.$this->configFixtureDataFile)) {
                throw new \Exception('configFixtureDataFile:'.$this->getDir().DIRECTORY_SEPARATOR.$this->configFixtureDataFile.' does not exists');
            }
            else $this->loadFixture($this->getDir().DIRECTORY_SEPARATOR.$this->configFixtureDataFile, $this->configDb);
        }
    
        if(!empty($this->tenantFixtureDataFile)) {
            if(!file_exists($this->getDir().DIRECTORY_SEPARATOR.$this->tenantFixtureDataFile)) {
                throw new \Exception('tenantFixtureDataFile:'.$this->getDir().DIRECTORY_SEPARATOR.$this->tenantFixtureDataFile.' does not exists');
            }
            else $this->loadFixture($this->getDir().DIRECTORY_SEPARATOR.$this->tenantFixtureDataFile, $this->tenantDb);
        }

    }
    
    /**
     * 清空基镜数据库，会在tearDown时自动调用
     * @throws \Exception
     */
    protected function dbDown() {
        if(!empty($this->configFixtureDataFile)) {
            if(!file_exists($this->getDir().DIRECTORY_SEPARATOR.$this->configFixtureDataFile)) {
                throw new \Exception('configFixtureDataFile:'.$this->getDir().DIRECTORY_SEPARATOR.$this->configFixtureDataFile.' does not exists');
            }
            else $this->unloadFixture($this->getDir().DIRECTORY_SEPARATOR.$this->configFixtureDataFile, $this->configDb);
        }
    
        if(!empty($this->tenantFixtureDataFile)) {
            if(!file_exists($this->getDir().DIRECTORY_SEPARATOR.$this->tenantFixtureDataFile)) {
                throw new \Exception('tenantFixtureDataFile:'.$this->getDir().DIRECTORY_SEPARATOR.$this->tenantFixtureDataFile.' does not exists');
            }
            else $this->unloadFixture($this->getDir().DIRECTORY_SEPARATOR.$this->tenantFixtureDataFile, $this->tenantDb);
        }
    
        if(!empty($this->configFixtureDataFile) || !empty($this->tenantFixtureDataFile)) {
            $this->unloadFixture(__DIR__.DIRECTORY_SEPARATOR.'_configDb.php', $this->configDb);
        }

        foreach($this->_db as $con) {
            $con->close();
        }
    }
    
    /**
     * 将$file中的数据加载到$dbname中去，用于数据准备
     * @param string $file 从当前路径寻址的文件名
     * @param string $dbname 数据导入的目标数据库名
     * @param boolean $truncate 是否清空原表中的数据
     * @throws Exception
     * @return array
     */
    protected function loadFixture($file,$dbname,$truncate=true) {
        $fixtures = $this->getFixture($file);
        $db = $this->getDb($dbname);
        $trans = $db->beginTransaction();
        try {
            foreach ($fixtures as $key => $values) {
                if($truncate) {
                    $db->createCommand()->truncateTable($key)->execute();
                }
                if(!empty($values)) {
                    $db->createCommand()->batchInsert($key, array_keys($values[0]), $values)->execute();
                }
            }
            $trans->commit();
            return $fixtures;
        }
        catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }
    
    /**
     * @param 清空fixture中涉及的表结构
     * @param string $file
     * @param string $dbname
     * @param bool $truncate 是否清空表数据（否则按行严格匹配删除）
     * @throws Exception
     */
    protected function unloadFixture($file,$dbname,$truncate=true) {
        $fixtures = $this->getFixture($file);
        $db = $this->getDb($dbname);
        $tables = array_keys($fixtures);
        $trans = $db->beginTransaction();
        try {
            foreach($tables as $table) {
                if($truncate) {
                    $db->createCommand()->truncateTable($table)->execute();
                }
                else {
                    foreach($fixtures[$table] as $line) {
                        $db->createCommand()->delete($table,$line)->execute();
                    }
                }
            }
            $trans->commit();
        }
        catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }
    
    protected function getFixture($file) {
        if(!isset($this->_fixtures[$file])) {
            $this->_fixtures[$file] = require $file;
        }
        return $this->_fixtures[$file];
    }
    
    /**
     * 单独清空某个表
     * @param string $dbname
     * @param string $table
     * @throws Exception
     */
    protected function truncateTable($dbname,$table) {
        $db = $this->getDb($dbname);
        $trans = $db->beginTransaction();
        try {
            $db->createCommand()->truncateTable($table)->execute();
            $trans->commit();
        }
        catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }
    
    protected function getDb($dbname) {
        if(!isset($this->_db[$dbname])) {
            $config = [
                'dsn' => 'mysql:host='.$this->testDbHost.';port='.$this->testDbPort.';dbname='.$dbname,
                'username' => $this->testDbUser,
                'password' => $this->testDbPasswd
            ];
            $db = new \yii\db\Connection($config);
            $this->_db[$dbname] = $db;
        }
        
        return $this->_db[$dbname];
    }
}
