<?php
namespace mysoft\db;

/**
 * DAL帮助类，用于辅助构造简单的sql语句，需要使用此工具类的DAL请`use DALTrait;`
 * 功能：
 * 关联数组插入某个表，
 * $this->_inserTable($tablename,['column1'=>'value1','column2'=>'value2']);
 * 
 * 用管理数组更新某个表,
 * $this->_updateTableById($tablename,['pk1'=>'pkv1','pk2'=>'pkv2'],['column1'=>'value1','column2'=>'value2']);
 * 
 * @author fangl
 *
 */
trait DAlTrait
{
    /**
     * 将$arr的key生成用于参数绑定的数组
     * @param array $arr
     * @return multitype:string
     */
    function _buildBindKey($arr) {
        $keys = array_keys($arr);
        $params = [];
        foreach($keys as $k) {
            $params[] = ':'.strtoupper($k);
        }
        return $params;
    }
    
    /**
     * @desc 为$arr生成用于参数绑定的' k = :K'参数绑定key数组，与_generateParams配合用于快速生成sql
     * @param array $arr
     * @param array $opcfg 某key的操作参数，比如['k1'=>'>','k2'=>'<']，将生成 ['k1 > :K1','k2 < :K2']
     * @return array
     */
    function _buildOpSql($arr,$opcfg=[]) {
        $keys = array_keys($arr);
        $sql = [];
        foreach($keys as $k) {
            //如果opcfg传来了对当前key的op，则用配置中的，默认=
            if(isset($opcfg[$k])) {
                $op = $opcfg[$k];
            }
            else $op = '=';
            $sql[] = $k.' '.$op.' :'.strtoupper($k); //构造 k = :K 这种形式的sql片段
        }
        return $sql;
    }
    
    /**
     * 将$arr构造成用于参数绑定的参数列表
     * @param array $arr
     * @return multitype:unknown
     */
    function _buildParams($arr) {
        $params = [];
        foreach($arr as $k=>$v) {
            $params[':'.strtoupper($k)] = $v;
        }
        return $params;
    }
    
    /**
     * 根据id更新表
     * @param string $tablename
     * @param mix $pk
     * @param array $infos
     */
    function _updateTableById($tablename,$pk,$infos) {
        if(!is_array($pk)) {
            $pk = ['id'=>$pk];
        }
        $pks = $this->_buildOpSql($pk);
        
        $sql = 'update '.$tablename.' set '.join(',',$this->_buildOpSql($infos)).' where '.join(' and ',$pks);
        return $this->db->createCommand($sql,$this->_buildParams(array_merge($infos, $pk)))->execute();
    }
    
    /**
     * 将数据插入
     * @param string $tablename
     * @param array $infos
     * @return boolean
     */
    function _insertTable($tablename,$infos) {
        $sql = 'insert into '.$tablename.' ('.join(',', array_keys($infos)).') values ( '.join(',',$this->_buildBindKey($infos)).' ) ';       
        if($this->db->createCommand($sql,$this->_buildParams($infos))->execute()) {
            return $this->db->getLastInsertID();
        }
        else return false;
    }
}
