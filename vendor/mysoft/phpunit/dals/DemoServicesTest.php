<?php
namespace test\phpunit\das;

use mysoft\base\PHPUnitTestCase;
class DemoServicesTest extends PHPUnitTestCase {
    
    public function testAA() {
        echo \Yii::getAlias('@common');
    }
}