<?php
namespace mysoft\base;
use mysoft\dbprovider\DbProvider;

class ServiceBase {
    protected $orgcode;
    /**
     *
     * @var \mysoft\dbprovider\DbProvider 
     */
    protected $dbProvider;
    protected $_current_date;
    
    public function __construct($orgcode,$enableSlaves=false)
    {
        $this->orgcode = $orgcode;
        $this->dbProvider = new DbProvider($orgcode,$enableSlaves);
        $this->_current_date = date( "Y-m-d H:i:s" );
    }
    /**
     * 
     * @param type $provider 数据Provider
     */
    public function setDbProvider($provider)
    {
        $this->dbProvider = $provider;
    }
    
    /**
     * service穿透dal时，methodname前拼接的route；会在service执行methodname时，默认执行fetch($dalroute+methodname)方法
     * @var string
     */
    public $dalroute;
    
    /**
     * 对于一些Service层只做中转的方法，这里提供默认的机制直接穿透Service，直接到dal层
     * 但是，需要在自己的Service配置$dalroute，__call魔术方法会自动拼接完整$route，并按照次序传递参数
     * @param string $name
     * @param mix $params
     * @return mixed
     */
    public function __call($name,$params) {
        if(!empty($this->dalroute)) {
            return $this->dbProvider->fetch($this->dalroute.'/'.$name,$params);
        }
        else throw new \Exception('method '.$name.' not exist');
    }
    
    public function __errRet($errcode='0',$errmsg='ok',$data=null)
    {
        $ret = [
            'errcode' => $errcode,
            'errmsg' => $errmsg,
            'data' =>$data
        ];
        return $ret;
    }
}
