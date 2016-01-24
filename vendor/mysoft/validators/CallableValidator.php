<?php
namespace mysoft\validators;

/**
 * 自定义函数校验器
 * 
 * 该校验器接收一个用户自定义函数作为校验过程函数，该函数接收欲校验的值，函数完成校验的过程，并返回一个数组，返回null或者空数组则表示校验通过。
 * 
 * 如，['txt','call','func'=>function($value) { if(strlen($value) > 0 && strlen($value) < 100) { return null; } return ['该字段长度必须为0-100',[]];}]
 * 
 * 注意：call为mysoft\helpers\Validator为此校验器起的校验别名
 * 
 * @author fangl
 *
 */
class CallableValidator extends \yii\validators\Validator {
			
    /**
     * 
     * @var function ($value) { return [$message,$params] }
     */
    public $func;

    public function validateValue($value) {
        return call_user_func($this->func,$value);
    }
}