<?php
namespace mysoft\helpers;
use Yii;
use ArrayAccess;
use ArrayObject;
use ArrayIterator;
use ReflectionClass;
use IteratorAggregate;
use yii\validators\Validator as Yii_Validator;
use yii\base\InvalidConfigException;

/**
 * 用户输入数据校验器，由于我们没有采用Yii的输入模型。这里基于Yii的校验器机制进行了一层封装，校验规则基本可以参考Yii的默认规则。
 * 
 * 1，使用
 * $validator = new \mysoft\helpers\Validator($rules);
 * $validator->validate($inputs);
 * 
 * $rules为校验器规则列表，里面的每一行为一个规则数组。
 * 格式为：[规则1，规则2]，其中规则n的格式为[校验属性(array|string)，校验器名，校验器初始参数]
 * 
 * 所有的内置校验器请参考：yii\validators\Validator::$builtInValidators
 * 
 * 
 * 2，$validator->getErrors()返回每个校验属性的最后一次错误，如不存在错误，返回空数组。如果某个校验属性无错误，在不存在属性名下标
 * 
 * 3，自定义错误消息：校验器初始参数传入'message'=>'你的错误消息'
 * 
 * 4，扩展自己的校验器（一般情况下yii完全能够满足需求）
 * 如果yii提供的校验器不能满足你的需求，你可以实现一个自己的校验器，方法是：
 * a，新建一个校验器类，继承自yii\validators\Validator
 * b，实现其::validateValue方法，该方法接收一个值，并校验该值是否符合校验器规则并返回一个错误信息数组。返回空数组代表校验成功。
 * 
 * 使用参考文档：http://git-whyd.mysoft.com.cn/whyd/devdocs/blob/master/%E5%BC%80%E5%8F%91%E6%96%87%E6%A1%A3/%E5%BC%80%E5%8F%91%E5%B8%AE%E5%8A%A9/Validator.md
 * 
 * @author fangl
 *
 */
class Validator {
    
    static $builtInValidators = [
        'call' => '\mysoft\validators\CallableValidator',
    ];
    
    private $availableValidators = [];
    
    private $rules = [];
    
    private $_validators = [];
    
    private $_errors = [];
    
    private $_lastError = null;
    
    /**
     * 
     * @param array $rules
     * @param array $validators
     */
    public function __construct($rules,$validators=[]) {
        $this->rules = $rules;
        //覆盖优先级：官方 < builtIn < 传进来的 
        $this->availableValidators = array_merge(Yii_Validator::$builtInValidators,self::$builtInValidators,$validators);
    }
    
    /**
     * 校验
     * @param array $inputs
     * @param string $validateAll 是否校验所有属性后返回，如果为false，则在校验出错即结束，默认为true
     * @return boolean 如果没有一行校验错，则返回false，否则返回true
     */
    public function validate($inputs,$validateAll=true) {
        $this->createValidators();
        $inputs = array_filter($inputs); //过滤掉空的
        foreach($inputs as $attr => $value) {
            if(isset($this->_validators[$attr])) {
                foreach($this->_validators[$attr] as $validator) {
                    $error = null;
                    if(!$validator->validate($value,$error)) {
                        $this->_errors[$attr] = $error;
                        $this->_lastError = "$attr:$error";
                        if(!$validateAll) {
                            return false;
                        }
                    }
                }
                unset($this->_validators[$attr]);//完成某个属性的校验，则移除校验器
            }
        }
        //如果还有未完成的校验器，说明属性为空，这个时候检查剩下的有无required校验器，有则验证出错
        if(!empty($this->_validators)) {
            foreach($this->_validators as $attr => $validators) {
                foreach($validators as $v) {
                    if(get_class($v) == $this->availableValidators['required']) {
                        $error = null;
                        $v->validate(null,$error);
                        $this->_errors[$attr] = $error;
                        $this->_lastError = "$attr:$error";
                        if(!$validateAll) {
                            return false;
                        }
                    }
                }
            }
        }
        return !$this->hasErrors();
    }
    
    private function createValidators() {
        $this->_validators = [];
        foreach($this->rules as $rule) {
            if(is_array($rule) && isset($rule[0],$rule[1])) { //attribute rule params
                $params = array_slice($rule, 2);
                $type = $rule[1];
                if(is_string($rule[0])) {
                    $this->appendValidator($rule[0], $this->createValidator($type, $params));
                }
                else if(is_array($rule[0])) {
                    foreach($rule[0] as $attr) {
                        $this->appendValidator($attr, $this->createValidator($type, $params));
                    }
                }
                else throw new InvalidConfigException('Invlid validation rule :'.json_encode($rule));
            }
        }
    }
    
    private function createValidator($type,$params=[]) {
        if($type instanceof Yii_Validator) {
            return $type;
        }
        else if(isset($this->availableValidators[$type])) {
            if($this->availableValidators[$type] instanceof Yii_Validator) {
                return $this->availableValidators[$type];
            }
            else {
                if(is_array($this->availableValidators[$type])) {
                    $params = array_merge($this->availableValidators[$type],$params);
                }
                else $params['class'] = $this->availableValidators[$type];
                return \Yii::createObject($params);
            }
        }
        else throw new InvalidConfigException('unknow validator type '.$type);
    }
    
    private function appendValidator($attr,$validator) {
        if(!isset($this->_validators[$attr])) {
            $this->_validators[$attr] = [];
        }
        $this->_validators[$attr][] = $validator;
    }
    
    /**
     * 本次校验是否有失败
     * @return boolean
     */
    public function hasErrors() {
        return !empty($this->_errors)?true:false;
    }
    
    /**
     * 获取校验错误数组
     * @return Ambigous <multitype:, NULL>
     */
    public function getErrors() {
        return $this->_errors;
    }
    
    
    /**
     * 获取最新一次错误信息
     * @return string
     */
    public function getLastError() {
        return $this->_lastError;
    }
    
}
