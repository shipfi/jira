<?php
namespace mysoft\web;

use mysoft\user\micro\AuthFactory;
use mysoft\user\micro\AuthException;
use mysoft\user\MicroIdentity;

/**
 * 
 * @author fangl 2015.10.20 重构
 *
 */
class FrontendController extends Controller {

    /**
     * 应用code
     * @var type
     */
    protected $appcode = '';
    
    protected $from;

    public function init() {
        parent::init();
        //identityclass在controller里面显示的指定
        \Yii::$container->set('yii\web\User',['identityClass'=>'mysoft\user\MicroIdentity']);
        \Yii::$app->user->identityClass = 'mysoft\user\MicroIdentity'; //针对autologin的场景
            
        $this->from = I('__from');

        if(empty($this->from)) {
            $this->from = cookie('__from@'.$this->orgcode);
        }
        else cookie('__from@'.$this->orgcode,$this->from);
        \mysoft\pubservice\PageVisitService::start($this->orgcode);
        
        if(IS_UPGRADING == true) {
            $demo_tenants = \mysoft\pubservice\Conf::getConfig('demo_tenants');
            $demo_tenants = json_decode($demo_tenants,true);
            if(empty($demo_tenants)) {
                $demo_tenants = [];
            }
            if(!in_array($this->orgcode, $demo_tenants)) {
                $this->redirect('/systemupgrade/index.html');
                \Yii::$app->end();
            }
        }
    }
    
    public function afterAction($action, $result) {
        \mysoft\pubservice\PageVisitService::end($this->orgcode);
        return parent::afterAction($action, $result);
    }
    
    public function beforeAction($action) {
        if(parent::beforeAction($action)) {
            
            if(! (\Yii::$app->user->getIdentity() instanceof MicroIdentity) || \Yii::$app->user->getIdentity()->orgcode != $this->orgcode){
                try {
                    $auth = AuthFactory::getAuth($this->orgcode, $this->from);
                    if (YII_ENV == "dev" || YII_ENV == "ci") {
                        $dev_account_id = I('dev_account_id',cookie('dev_account_id@'.$this->orgcode));
                        if(!empty($dev_account_id)) {
                            cookie('dev_account_id@'.$this->orgcode,$dev_account_id);
                        }
                        $auth->setDevAccountId($dev_account_id);
                    }
                    return $auth->login();
                }
                catch (AuthException $e) {
                    //正式环境下，对于frontend里面抛出的异常进行捕获并友好化展示。开发环境，或者其他的异常编码向上抛出
                    if(YII_ENV != 'dev') {
                        \Yii::error($e->getMessage(),__METHOD__);
                        \Yii::$app->response->content = $this->renderPartial('@vendor/mysoft/web/views/unAuthorized/selfexception',['msg'=>$e->getMessage()]);
                        return false;
                    }
                    else throw $e;
                }
            }
            else {
                //针对第三方集成的场景，将authcode自动通过跳转的方式隐藏掉
                $authparams = \mysoft\pubservice\BasicParams::get($this->orgcode, 'third_app_user_code_params');
                if(!empty($authparams)) {
                    $authcode = I($authparams);
                    if(!empty($authcode)) {
                        $query = \Yii::$app->request->getQueryParams();
                        if(isset($query[$authparams])) {
                            unset($query[$authparams]);
                        }
                        \Yii::$app->response
                        ->redirect(\Yii::$app->request->getHostInfo().\Yii::$app->params['static_host'].'/'.\Yii::$app->request->getPathInfo()."?".http_build_query($query))
                        ->send();
                        return false;
                    }
                }
            }
    
            //首先验证租户的应用授权
            if($this->check_access_appauth()){
                return true;
            }
            else{
                $msg = '该应用还没有授权哦！';
            }
            \Yii::$app->response->content = $this->renderPartial('@vendor/mysoft/web/views/unAuthorized/unAuthorized',['msg'=>$msg]);
        }
        return false;
    }
    
    //检查当前租户是否有权限访问应用
    public function check_access_appauth()
    {
        if(empty($this->appcode)) {
            $this->appcode = @\Yii::$app->params['app_code'];
        }
    
        if(empty($this->appcode) || empty($this->orgcode)){ //没设置appcode属性的直接pass
            return true;
        }
    
        $authapps  = $this->getAppsAuth($this->orgcode);
    
        if(isset($authapps[$this->appcode])){ //有访问权限
            return true;
        }
    
        return false;
    }
    
    /**
     * 获取某个租户的授权应用
     * @param type $tenant_id 租户标识
     * @return type 授权应用列表
     */
    protected  function getAppsAuth($tenant_id) {
        //注册apps_auth_{orgcode} by fangl
        $cache_key = ['apps_auth_{orgcode}',$tenant_id];
        $authapps = \Yii::$app->cache->get($cache_key);
         
        if($authapps){
             
            return $authapps;
             
        }else{
             
            $sql = "select tenant_id,b.* from p_app_author as a left join p_apps as b on a.app_id = b.app_id where a.tenant_id= :tenant_id ";
            $res = DB('config')->createCommand($sql,['tenant_id'=>$tenant_id])->queryAll();
             
            $authapps = [];
             
            if($res){
                foreach($res as $rs){
                    $authapps[$rs['app_code']] = $rs;
                }
            }
             
            \Yii::$app->cache->set($cache_key,$authapps,60);
             
            return $authapps;
             
        }
    }
}