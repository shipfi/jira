<?php
namespace mysoft\base;

/**
 * Description of PHPUnit_TestCaseBase
 *
 * @author Young
 */
class PHPUnitTestCase extends \PHPUnit_Framework_TestCase{
    
    use \mysoft\base\MockTrait;
    
    
    
    /**
     * 参数校验
     * @param type $service service对象
     * @param String $method  测试方法
     * @param type $param 参数
     * @param type $exceptionCode 异常编码
     */
    protected function assertException($service,$method,$param,$exceptionCode) {
        try{
            call_user_func_array(array($service, $method), $param);
        }
        catch(\Exception $e)
        {
            $this->assertEquals($e->getCode(), $exceptionCode);
        }
    }

}
