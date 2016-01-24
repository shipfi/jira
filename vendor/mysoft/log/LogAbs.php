<?php

/**
 * 日志服务抽象类
 * 提供规范的方法和抽象方法
 * @author yangzhen<yangz03@mysoft.com.cn>
 */

namespace mysoft\log;

use yii;
use yii\base\Component;
/**
 * Abstract Class: LogAbs
 *
 * @author yangzhen
 */
abstract class LogAbs extends Component
{
    /**
     * 日志类型
     * key=>value, 日志类型简写=>日志库.表(OR 索引名.类型)
     * @var array 
     */
    protected $log_type =[
       'demo'   => 'demo.test2',    //测试示例
       'common' => 'mysoft.common', //通用日志模式 
       'debug'  => 'mysoft.debug',  //debug用日志模式
    ];
   
   
    public $debug = false;
    
    /**
     * 日志应用属性
     * @var type 
     */
    public  $app;
    
   
    
    /**
     * 组件Component初始化init重写
     */
    public function init() 
    {   parent::init();
        $this->_init();//继承类里实现该方法
    }
    
    
    /**
     * 检查日志类型
     * @param string $type
     * @return strng
     * @throws \Exception
     */
    protected function check_log_type($type)
    {        
       
      if(!isset($this->log_type[$type]))
      {
          throw new \Exception('日志类型错误，请核查！');
      }
         
       return  $this->log_type[$type];
    }

    
    
    /**
     * 每个继承的子类去实现对类型的解析
     * 如:database.table or index.type
     * @param string $logtype
     * @return array|string
     */
    abstract protected function parseLogType($logtype);


    /**
     * 设置日志类型
     * @param string $logtype
     * @return \mysoft\log\LogAbs
     */
    public function setLogType($logtype)
    {    
//         $types = $this->check_log_type($logtype);
         if(empty($logtype)){
              throw new \Exception('请设置下app的属性值');
         }
         $types  = sprintf('%s.%s',$this->app,$logtype);
         $this->parseLogType($types);
         return $this;       
    }
    
    
    
    /**
     * 设置debug模式
     * @param boolean $debug
     * @return \mysoft\log\LogAbs
     */
    public function setLogDebug($debug=false)
    { 
        $this->debug = (bool)$debug;
        return $this;
    }
    
    
    /**
     * 写日志操作
     * @param array|string $data
     * @param string $logtype ,设置日志类别，这里传值则无须调用setLogType
     * @return mixed
     */
    public function logging($data,$logtype='')
    {
       if($logtype) $this->setLogType ($logtype);       
       return $this->create($data);
    }
    
    
    /**
     * 更新日志
     * @param string|int    $id
     * @param array|string  $data
     * @param string `      $logtype
     * @return mixed
     */
    public function updateLog($id,$data,$logtype='')
    {
        if($logtype)$this->setLogType ($logtype);
        return $this->update($id, $data);
    }
    
    
    /**
     * 清理日志
     * @param string $logtype
     * @return mixed
     */
    public function clearLog($logtype=''){
        if($logtype)$this->setLogType ($logtype);
        return $this->clear();
    }
    
    
    /**
     * 写操作
     * @abstract
     * @access protected
     * @param array|string $data
     */
    abstract protected function create($data);
    
    /**
     * 更新操作
     * @abstract 
     * @access protected
     * @param string|int    $id 更新的标识
     * @param string|array  $data 更新的数据
     */
    abstract protected function update($id,$data);


    /**
     * 清理日志，根据日志类型
     * @abstract
     * @access protected
     * @return mixed
     */
    abstract protected function clear();


    //读日志
//    abstract protected function read();
    
    
}
