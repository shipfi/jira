<?php
/*
 * task库的数据操作
 * 处理:
 * task_lists - 任务表
 * task_rules - 任务规则表
 * task_log   - 任务日志
 */

namespace mysoft\task;

/**
 * Task of DataService
 *
 * @author yangzhen
 */
class DataService 
{
    use Com { task_db as protected;}//继承Trait
    /**
     * task_db数据库对象
     * @var object 
     */
    private $_taskdb;
    
    const TK_RULES   = 'task_rules';  //规则表
    const TK_LISTS   = 'task_lists';  //任务表
    const TK_LOG     = 'task_log';    //任务执行日志表

    public function __construct() 
    {
       $this->_taskdb = $this->task_db();
    }
     
     
     /**
      * 检查字段，缺失字段抛出异常，否则返回值
      * @param string $field
      * @param array $data
      * @param string $throw
      * @return array
      * @throws Exception
      */
     private function _check_field(string $field, array $data, string $throw)
     {
          if(!isset($data[$field])){
               throw new \Exception($throw);
          }
          
          return $data[$field];
     }




     /**
      * 注册任务
      * @param type $data
      * @return int|string
      */
     public function register_task($data)
     {
         $_data               = [];
         $_data['orgcode']    = $this->_check_field('orgcode', $data, '缺失字段[orgcode]');
         $_data['task_type']  = $this->_check_field('task_type',$data,'缺失字段[task_type]');
         $_data['path']       = $this->_check_field('path', $data, '缺失字段[path]');
         $_data['task']       = $this->_check_field('task', $data, '缺失字段[task]');
         $_data['start_time'] = $this->_check_field('start_time',$data,"缺失字段[start_time]");
        
         //运行脚本格式检查
         if(!preg_match('#^[a-z|-]+/[a-z|-]+$#',$_data['task'])) throw new \Exception('注意task的格式必须是 xxx/xxx,前者是控制器，后者是方法');
         
         //处理任务周期问题
         if(!isset($data['interval'])) $_data['interval'] = 3600; //单位秒
         else  $_data['interval'] = $data['interval'];         
         
         return $this->_taskdb->createCommand()->insert(self::TK_LISTS, $_data)->execute();
           
     }
     
     
     //注销任务
     public function unregister_task($task_id)
     {
        return $this->_taskdb->createCommand()
                    ->delete(self::TK_LISTS,"task_id = :task_id",[':task_id'=>$task_id])
                    ->execute();
     }

     
     //查询任务信息
     public function search_task($orgcode='',$task_type='')
     {
         $sql  = "select * from `".self::TK_LISTS ."` ";
         
         $whereStr = [];
         $bind     = [];
         
         if($orgcode){
             $whereStr[] = " orgcode = :orgcode ";
             $bind[':orgcode'] = $orgcode;
         }
         
         if($task_type){
             $whereStr[] = " task_type = :task_type ";
             $bind[':task_type'] = $task_type;
         }
         
         if($whereStr){
              $whereStr = implode(' AND ', $whereStr);
              $sql .= "where {$whereStr}";
         }
             
         return $this->_taskdb->createCommand($sql,$bind)->queryAll();
             
     }
     
     //添加规则
     public function add_task_rules($data)
     {
          $_data              = [];
          $_data['task_type'] = $this->_check_field('task_type',$data,"缺失字段[task_type]");
          $_data['orgcode']   = $this->_check_field('orgcode',$data,"缺失字段[orgcode]");
          $_data['nums']      = $this->_check_field("nums",$data,"缺失字段[nums]");
          
          if($_data['nums'] > 5) throw new \Exception('nums限制输入超过5');
          
          return $this->_taskdb->createCommand()->insert(self::TK_RULES, $_data)->execute();
 
     }
     
     
     //移除规则
     public function remove_task_rules($task_rule_id)
     {
         return $this->_taskdb->createCommand()
                     ->delete(self::TK_RULES, "task_rule_id = :task_rule_id",[':task_rule_id'=>$task_rule_id])
                     ->execute();
     }
     
     //查阅日志
     public function search_log($search=[])
     {
          //TODO select
     }
              
     
}
