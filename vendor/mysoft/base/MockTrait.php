<?php
namespace mysoft\base;

trait MockTrait {
    /**
     *
     * @param type $service Provider所在对象
     * @param array $expect 期望值[$rount=$expectValue]
     * @return type
     */
    protected function mockDbProvider($service, $expect) {
        $stub = $this->getMockBuilder('\\mysoft\\dbprovider\\DbProvider')
        ->disableOriginalConstructor(["orgcode" => ""])
        ->getMock();
        $service->setDbProvider($stub);
        $stub->method('fetch')
        ->will($this->returnCallback(function ($arg) use ($expect) {
            foreach ($expect as $route=> $expectVal) {
                if ($route == $arg) {
                    //判断路由是否存在,存在，则返回一个ReflectionMethod对象，否则抛出异常
                    $ref = $this->checkRouteIsExist($route);
    
                    //检查函数的调用参数个数是否匹配
                    $args = func_get_args();
                    $args = array_slice($args, 1);
                    $argsMust = 0;
                    for($i=0; $i<count($ref->getParameters()); $i++) {
                        $p = $ref->getParameters()[$i];
                        if(!$p->isOptional()) {
                            $argsMust ++;
                        }
                    }
    
                    //入参必须大于等于必要参数个数，否则异常
                    if( count($args[0]) < $argsMust ) {
                        throw new \Exception(($ref->getDeclaringClass()?$ref->getDeclaringClass()->getName():'')."::".$ref->getName()." need at least {$argsMust} arguments, ".count($args[0])." given");
                    }
                    else {
                        //如果是回调函数，则按照原声明格式调用回调函数
                        if(is_callable($expectVal)) {
                            return call_user_func_array($expectVal, $args[0]);
                        }
                        else return $expectVal;
                    }
                }
            }
        }));
    
            return $stub;
    }
    
    /**
     * mock某对象的某个返回值，
     * @param string $clsname 欲mock的某个类名
     * @param array $returns ['methodname'=>ret] 格式
     * @return stubobj 对clsname的mock桩件
     */
    protected function mockInstance($clsname,$returns) {
    
        $stub = $this->getMockBuilder($clsname)
        ->disableOriginalConstructor()
        ->getMock();
        return $this->mockMethod($stub, $returns);
    }
    
    /**
     * 给某个mock的stub桩件增加新的mock方法
     * @param mock $stub
     * @param array $returns  ['methodname'=>ret] 格式
     * @return stub
     */
    protected function mockMethod($stub, $returns) {
        foreach($returns as $method=>$ret) {
            $stub->method($method)->will($this->returnCallback(function() use($ret) {
                if(is_callable($ret)) {
                    $arg = func_get_args();
                    return call_user_func_array($ret, $arg);
                }
                else return $ret;
            }));
        }
        return $stub;
    }
    
    /**
     * 对于非通过dal调用数据库的场景，此方法会mock一个Connection对象,模拟其createCommand方法，返回一个\yii\db\Command
     * 对于大家简单的调用DB()->createCommand($sql,$params)
     * ->execute/query/queryAll/queryOne/queryScalar/queryColumn,支持根据sql返回模拟值
     * Command的其他方法返回null
     * @param unknown $returns
     * @throws \Exception
     * @return \yii\db\Connection
     */
    protected function mockDb($returns) {
    
        $dbMockData = [
            'createCommand'=>function($sql,$params) use ($returns) {
                $execMockFunc = function() use ($returns,$sql,$params) {
                    if(isset($returns[$sql])) {
                        if(is_callable($returns[$sql])) {
                            return call_user_func($returns[$sql],[$params]);
                        }
                        else return $returns[$sql];
                    }
                    else throw new \Exception("unmocked sql {$sql} has been executed");
                };
    
                $commandMockData = [
                    'execute'=>$execMockFunc,
                    'query'=>$execMockFunc,
                    'queryAll'=>$execMockFunc,
                    'queryOne'=>$execMockFunc,
                    'queryScalar'=>$execMockFunc,
                    'queryColumn'=>$execMockFunc,
                ];
                return $this->mockInstance('\\yii\\db\\Command', $commandMockData);
            }
        ];
    
        return $this->mockInstance('\\yii\\db\\Connection', $dbMockData);
    }
    
    /**
     * 模拟I方法和cookie的值
     * @param array $req
     * @param array $cookies
     * @return void
     */
    protected function mockRequest($req,$cookies=[]) {
        \Yii::$app->request->setQueryParams($req);
        foreach($cookies as $k=>$v) {
            cookie($k,$v);
        }
    }
    
    private function checkRouteIsExist($route)
    {
        $arr = explode('/', $route);
    
        $module = $dal_class = $method ='';
    
        switch (count($arr))
        {
            case 2:
                $dal_class = ucfirst($arr[0]);
                $method	  = $arr[1];
                $className = "\\dals\\".$dal_class."DAL";
                break;
            case 3:
                $module	  = $arr[0];
                $dal_class = ucfirst($arr[1]);
                $method	  = $arr[2];
                $className = "\\dals\\".$module."\\".$dal_class."DAL";
                break;
    
        }
    
        //throw Exception if not exist
        $ref = new \ReflectionMethod($className, $method);
    
        return $ref;
    }
}