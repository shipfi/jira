<?php
namespace mysoft\web;
use yii\filters\AccessControl;

/**
 * 应用场景：某个controller的方法需要登录之后才能访问，请在controller里面用`use \mysoft\web\NeedAuthTrait;`使用本trait
 * 这样，当未登录用户访问时，会跳转到webuser/loginUrl配置中的地址去登录
 * 
 * 如果你需要对自己的controller做精细的授权（比如某些action可以访问，某些不可以）可以自定义only和rules属性，
 * 详细参考：
 * http://www.yiichina.com/doc/api/2.0/yii-filters-accesscontrol
 * 
 * @author fangl
 *
 */
trait NeedAuthTrait {
  
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [], //filter应用于所有的action
                'rules' => [
                    [
                        'actions' => [],    //此规则应用所有的action（只允许登录访问)
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }
}