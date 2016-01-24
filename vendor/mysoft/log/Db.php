<?php
/**
 * 通过数据表形式记录日志
 * 
 * @author yangzhen <yangz03@mysoft.com.cn>
 */
namespace mysoft\log;
/**
 * Description of Db
 *
 * @author yangzhen
 */
class Db extends LogAbs
{
    /**
     * 数据库
     * @var string
     */
    private $database;
    
    /**
     * 数据表
     * @var string
     */
    private $table;
    


    protected function parseLogType($logtype) 
    {
        list($this->database,$this->table) = explode('.',$logtype);    
    }
    
    
    
    protected function create($data) 
    {
        //TODO
    }
    
    
    protected function update($id,$data)
    {
        
    }
}
