<?php

/**
 * 通过Elastic Search提供日志服务
 * 
 * search ,mapping,create document,indexing
 * @author yangzhen<yangz03@mysoft.com.cn>
 */

namespace mysoft\log;

use Elastic\Client;

class Elastic extends LogAbs {

    /**
     * 设置Hosts，可以通过外部设置该参数值
     * @var array
     */
    public $hosts = [];

    /**
     * 配置参数
     * @var array
     */
    private $params = [];

    /**
     * Elastic
     * @var  \Elastic\Client
     */
    private $client;

    /**
     * 索引名
     * @var string string
     */
    private $index;

    /**
     * 类型名 ,mapping 
     * @var sting
     */
    private $type;

    /**
     * 初始化配置
     */
    protected function _init() {
        //读系统配置 from conf
        $conf_hosts = \mysoft\pubservice\Conf::getConfig('elastic_hosts');
        $this->params['hosts'] = explode(',', $conf_hosts);

        if ($this->hosts && is_array($this->hosts)) {//如果有外部设置则直接替代，便于local调试
            $this->params['hosts'] = $this->hosts;
        }

        $this->client = new Client($this->params);
    }

    /**
     * 实现父类抽象方法解析需要的 index,type for ealstic search
     * @param type $logtype
     */
    protected function parseLogType($logtype) {

        list($this->index, $this->type) = explode('.', $logtype);
        
    }

    /**
     * 检查参数，这里强制添加一个时间戳帮助排序
     * @param array|string $data
     */
    private function _check_data(&$data,$isupdate=false) {
        
        if(!$isupdate){
            
            $_t = microtime(true) * 10000;
            if (is_array($data)) {
                $data['_t'] = $_t;
            } else {
                $text = $data;
                $data = [];
                $data['text'] = $text;
                $data['_t'] = $_t;
                unset($text);
            }

        }
        

        
        foreach($data as $key=>&$val){
            
            if(is_string($val) && strtotime($val)){//对时间格式的处理
                $val = str_replace(' ','T',$val);    
            }
        }
        
        
    }

    /**
     * 写日志操作
     * @param array|string $data
     * @return string 插入成功的id
     * @throws \mysoft\log\Exception
     */
    protected function create($data) {

        try {
            $this->_check_data($data);

            $result = $this->client->createDoc($this->index, $this->type, $data);
            if (isset($result['_id']))
                return $result['_id'];
            throw new \Exception(print_r($result, true)); //对于结构不对的直接抛出异常
        } catch (\Exception $ex) {
            if ($this->debug === false)
                return false;
            throw $ex;
        }
    }

    /**
     * 更新操作
     * @param string|int ` $id
     * @param array|string $data
     * @return array
     * @throws \mysoft\log\Exception
     */
    protected function update($id, $data) {
        try {
            $this->_check_data($data,true);
            
            $res = $this->client->updateDoc($this->index, $this->type, $id, $data);
            if ($this->debug)
                return $res;
            return true;
        } catch (\Exception $ex) {
            if ($this->debug === false)
                return false;
            throw $ex;
        }
    }

    /**
     * 搜索服务
     * @param string|array  $query, 'field1:>0 AND field2:xxxx' or ['field'=>[tag1,tag2,tag3]],如果是数组则支持某个字段上的模糊查询
     * @param int           $page ,当前页码
     * @param int           $pagesize ,每页数量
     * @param string        $sort,排序，规则 'field:desc',默认 按内置_t降序
     * @return type
     * @throws \mysoft\log\Exception
     */
    public function search($query = '', $page = 1, $pagesize = 10, $sort = '_t:desc') {

        try {
            $search = [];
            $search['index'] = $this->index;
            $search['type'] = $this->type;
            

            if ($sort) {
                $search['sort'] = $sort;
            }

            if ($query) {

                if (is_array($query)) { 
                    
                    $must = [];
                    foreach ($query as $key => $val) {
                       
                        //区间值匹配
                        if(isset($query[$key]['range'])){
                            $keys = array_keys($val['range']);
                            
                            foreach($keys as $k){
                                
                                if(!in_array($k, ['lt','gt','from','to'])){
                                    throw new \Exception('关键字rang有问题,请使用gt,lt,from,to');
                                }
                                
                            }
                            
                            $must[]['range'][$key]=$val['range'];
                        }
                        
                        //单个字段的模糊查询
                        if(isset($query[$key]['like'])){
                            if(!is_array($val['like'])){
                                 throw new \Exception('关键字like的值请输入数组');
                            }
                            $val['like'] = implode(' ', $val['like']);
                            $must[]['match'][$key]= [
                                 'query'=>$val['like'],
                                 'operator'=>'AND' //默认配置  
                            ];
                           
                        }
                        
                        //单个字段精确匹配
                        if(isset($query[$key]['eq'])){
                            $must[]['match_phrase'][$key]=$val['eq'];
                        }
                        
                        
                    }
//                   echo '<pre>'; print_r($must);die;
                    if($must) {
                         $search['body']['query']['bool']['must']= $must;
                    }

                } else {//普通的查询
                    $search['body']['query']['query_string']['query'] = $query;
                }
            }


            $search['size'] = $pagesize > 0 ? $pagesize : 10;
            $search['from'] = ( ($page > 0) ? ($page - 1) : 1 ) * $search['size'];
            
            $res = $this->client->search($search);
            return $this->client->_format($res);
        } catch (\Exception $ex) {

            throw $ex;
        }
    }

    /**
     * 实现清理功能根据 map(index,type)
     * @return mixed
     */
    public function clear() {
        try {
            $res = $this->client->indices()->deleteMapping(['index' => $this->index, 'type' => $this->type]);
            if ($this->debug)
                return $res;
            return true;
        } catch (\Exception $ex) {
            if ($this->debug === false)
                return false;
            throw $ex;
        }
    }

    /**
     * 获取当前日志类别的总数
     * @return int
     */
    public function getCount($query = '') {
        $res = $this->client->getCount($this->index, $this->type, $query);
        return isset($res['count']) ? $res['count'] : 0;
    }
    
    
    //设置mappings
    public function putMapping($mappings)
    {
        $params = [];
        $params['index'] = $this->index;
        $params['type']  = $this->type;
        
        $mapping = [];
        $mapping['_source'] = ['enable'=>true];
        $properties = [];
        
        foreach($mappings as $field=>$config){
              $properties[$field] = [];
              if(!isset($config['type'])){
                  throw  new \Exception('缺少属性字段类型');
              }
              
              $properties[$field]['type'] = $config['type'];
              
              if(isset($config['format'])){
                  $properties[$field]['format'] = $config['format'];
              }
              
        }
        
        $mapping['properties'] = $properties;
        
        $params['body'][$this->type]=$mapping;
        
        return $this->client->indices()->putMapping($params);
        
    }
    
    //获取mapping的信息
    public function getMapping()
    {
         $params = [];
         $params['index'] = $this->index;
         $params['type']  = $this->type;
         
         return $this->client->indices()->getMapping($params);
    }
    
    //创建索引
    public function createIndex()
    {
        $params = [];
        $params['index']=$this->index;
        return $this->client->indices()->create($params);
    }
    
    
    

}
