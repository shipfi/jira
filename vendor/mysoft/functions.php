<?php

/**
 * 数据对象方法
 * @param string $orgcode
 * @param string $type  	master(可读可写)|slave（只读实例） 主从模式选择 ,默认是主实例，可读可写
 * @param bool   $auto_open 是否自动完成初始化open操作，兼容操作
 * @throws Exception
 * @return \yii\db\Connection  返回主从对象，根据type来判断
 */
function DB($orgcode="",$auto_open = true)
{
    $orgcode = empty($orgcode) ? I("__orgcode") : $orgcode;

    static $dbObj=[];
    if(empty($orgcode)){
        throw new Exception("未找到租户编码", "110000");
    }

    $dbObj_key = $orgcode; // 租户主从类型对象标识

    if(isset($dbObj[$dbObj_key]))
    {
        return $dbObj[$dbObj_key];
    }

    
    if($orgcode=="config")
    {
        $db = \Yii::$app->db;
    }  elseif ($orgcode == 'log') {
        $db = \Yii::$app->logDb;
    }  elseif ($orgcode == 'task') {
        $db = _get_taskdb();
    }
    elseif(in_array($orgcode, array_keys(\Yii::$app->params['other_dbs']))) {
        $dbconfig = \Yii::$app->params['other_dbs'][$orgcode];
        $db = \Yii::createObject($dbconfig);
    }
    else {
        $dbconfig = _get_dbconfig($orgcode);
        $db = new \yii\db\Connection($dbconfig);
    }


    if( $auto_open ) $db->open(); //是否开启自动连接模式，兼容只调用DB方法默认为开启状态

    $dbObj[$dbObj_key] = $db;

    return $db;
}

function _get_dbconfig($orgcode){
    $connstr_cachekey = ["ConnectionString_{orgcode}",$orgcode];
    //$connstr_cachekey = "ConnectionString_".$orgcode;
    $res = Yii::$app->cache->get($connstr_cachekey);
    if(empty($res)){
        $db = DB("config");
        $sql = "select * from p_tenants where tenant_id=:tenant_id";
        $orginfo = $db->createCommand($sql,[':tenant_id' => $orgcode])->queryOne();               
        if(empty($orginfo)){
            throw new Exception("租户{$orgcode}数据库链接为空！", "110001");
        }
        $db_instance = $db->createCommand("select * from p_instance where instance_id=".$orginfo['instance_id'])->queryOne();

        $res = [
            'dsn' => 'mysql:host='.$db_instance["host"].';port='.$db_instance['port'].';dbname='.$orginfo["db_name"],
            'username' => $db_instance["user_name"],
            'password' => $db_instance["password"]
        ];
        $db_instance_slaves = $db->createCommand("select * from p_instance where master_instance_id=".$orginfo['instance_id'])->queryAll();

        if(!empty($db_instance_slaves))
        {
            $res['slaveConfig'] = [
                'username' => $db_instance_slaves[0]['user_name'],
                'password' => $db_instance_slaves[0]['password'],
                'attributes' => [
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
            ];
            $res['slaves'] = [];
            foreach($db_instance_slaves as $li)
            {
                if(empty($li['host'])) continue;
                $res['slaves'][] = ['dsn'=>'mysql:host='.$li['host'].':'.$db_instance['port'].';dbname='.$orginfo["db_name"]];
            }
        }


        Yii::$app->cache->set($connstr_cachekey,$res);
    }

    return $res;
}

function _get_taskdb(){
    $conf  = \mysoft\pubservice\Conf::getConfig('task_db_config');
    if(empty($conf)) throw new \Exception ("task_db config not in configsettings");
    $conf  = json_decode($conf,true);
    $dbname = 'task';//默认数据库名
    if(isset($conf['dbname']) && $conf['dbname']){//便于调试如果配置里填写过dbname的话这里直接替换
        $dbname = trim($conf['dbname']);
    }
    $conn_arr = [
        'dsn' => 'mysql:host='.$conf["host"].';port='.$conf['port'].';dbname='.$dbname,
        'username' => $conf["user_name"],
        'password' => $conf["password"]
    ];
    return new \yii\db\Connection($conn_arr);
}

/**
 * 获取多结果集的数据
 * @param $dsninfo 租户编码orgcode或者数据库dbconfig
 * @param $query 查询语句
 * @param $opt 查新参数 ["type"=>"slave","timeout"=>30]
 * @return array
 * @throws Exception
 */
function multi_query($dsninfo,$query,$opt=["type"=>"slave","timeout"=>30]){
    if(empty($opt["type"])){
        $opt["type"]="slave";
    }
    if(empty($opt["timeout"])){
        $opt["timeout"]=30;
    }
    if(is_array($dsninfo)) {
        $dbconfig = $dsninfo;
    }
    else {
        $dbconfig = _get_dbconfig($dsninfo,$opt["type"]);
        $conn_string = ltrim($dbconfig["dsn"],"mysql:");
        $conn_parts = explode(";",$conn_string);
        foreach ($conn_parts as $part) {
            $partarr = explode("=",$part);
            $dbconfig[$partarr[0]] = $partarr[1];
        }
    }

    $mysqli=mysqli_connect($dbconfig["host"],$dbconfig["username"],$dbconfig["password"],$dbconfig["dbname"],$dbconfig["port"]);
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $opt["timeout"]);
    $tables = [];
    try{
        if($mysqli->multi_query($query)){
            do{
                if($result=$mysqli->store_result()){
                    $tables[] = $result->fetch_all(MYSQLI_ASSOC);
                    $result->free();
                }
            }while($mysqli->more_results() && $mysqli->next_result());
        }
        return $tables;
        $mysqli->close();
    }catch (\Exception $e){
        $mysqli->close();
        throw $e;
    }
}

function I($name,$default="")
{
    $val = \Yii::$app->request->get($name);
    $val = $val!=NULL?$val : \Yii::$app->request->post($name);
    return $val!=NULL?$val : $default;
}

/**
 * 抛出异常处理
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @return \mysoft\base\Exception
 * @throws Exception
 */
function E($msg, $code=0)
{
    return new \mysoft\base\Exception($msg, $code);
}

/**
 * 链接
 *
 *  * // /index?r=site/index
 * echo U('site/index');
 *
 * // /site/index&src=ref1#name
 * echo U(['site/index', 'src' => 'ref1', '#' => 'name']);
 *
 * // http://www.example.com/site/index
 * echo U('site/index', true);
 *
 * // https://www.example.com/site/index
 * echo U('site/index', 'https');
 *
 * @param string|array $route
 * @param string|boolen $scheme
 *
 * @return string
 */
function U($route,$scheme = false){
    $orgcode = I('__orgcode');
    
    $route = (array) $route;

    if($orgcode && empty($route['__orgcode'])) {
        $route['__orgcode'] = $orgcode;
    }
    
    if(!empty($route['__orgcode'])) {
        $route['__from'] = cookie('__from@'.$route['__orgcode']);
    }
    
    //这里拼接static_host的原因是nginx反向代理时，pathinfo中无虚拟目录
    if(!$scheme) {
        $static_host = isset(\Yii::$app->params['static_host'])?\Yii::$app->params['static_host']:'';
    }
    else $static_host = '';
    return $static_host.\yii\helpers\Url::toRoute($route,$scheme);
}

/**
 * Cookie 设置、获取、删除
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $options cookie参数
 * @return mixed
 */
function cookie($name='', $value='', $option=null) {
	
	$params = \Yii::$app->params['Cookie'];//获取全局COOKIE参数设置
	
    // 默认设置
    $config = array(
        'prefix'    =>  $params['COOKIE_PREFIX'], // cookie 前缀
        'expire'    =>  $params['COOKIE_EXPIRE'], // cookie 保存时间
        'path'      =>  $params['COOKIE_PATH'], // cookie 保存路径
        'domain'    =>  $params['COOKIE_DOMAIN'], // cookie 有效域名
        //'secure'    =>  $params['COOKIE_SECURE']? : false, // cookie should be sent via secure connection
        'httponly'  =>  $params['COOKIE_HTTPONLY'] ? : true, // httponly设置
    );
    
    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        $config     = array_merge($config, array_change_key_case($option));
    }
    
    if(!empty($config['httponly'])){    	
        ini_set("session.cookie_httponly", 1);
    }

    $name = $config['prefix'] . str_replace('.', '_', $name);

    // $value === '' 获取cookie
    if ('' === $value)
    {
        $value = \Yii::$app->request->cookies->getValue($name)?\Yii::$app->request->cookies->getValue($name):\Yii::$app->response->cookies->getValue($name);
        //针对enableCookieValidation = false场景下，忽略掉enableCookieValidation = true 规则生成的cookie
//         if(strlen($value) > 64 && $value{64} == 'a' && !\Yii::$app->getRequest()->enableCookieValidation) {
//             return null;
//         }
//         else return @unserialize($value)?unserialize($value):$value; //兼容已有的cookie（没有serilize）
        return @unserialize($value)?unserialize($value):$value; //兼容已有的cookie（没有serilize）
    }
    //删除cookie
    elseif($value === null)
    {
    	return \Yii::$app->response->cookies->remove($name);
    }
    //设置cookie
    else
    {
        $obj = [
            'name'      => $name,
            'value'     => serialize($value)
        ];
        $config['expire']  && $obj['expire'] = $config['expire'];
        $config['path']  && $obj['path'] = $config['path'];
        $config['domain']  && $obj['domain'] = $config['domain'];
        $config['httponly']  && $obj['httpOnly'] = $config['httponly'];

        return \Yii::$app->response->cookies->add(new \yii\web\Cookie($obj));
    }
}


function _include($viewFile, $params = [])
{   
    $viewFile = \Yii::getAlias($viewFile);
    
    if (is_file($viewFile)) {
        $viewFile = yii\helpers\FileHelper::localize($viewFile);
    } else {
        throw new \yii\base\InvalidParamException("The view file does not exist: $viewFile");
    }
    
    ob_start();
    ob_implicit_flush(false);
    extract($params, EXTR_OVERWRITE);
    require($viewFile);
    return ob_get_clean();
}

function _include_once($viewFile, $params = [])
{
    static $_included = [];
    if(isset($_included[md5($viewFile)])) {
        return false;
    }
    else {
        $_included[md5($viewFile)] = $viewFile;
        return _include($viewFile, $params);
    }
}

/**
 * 取api接口的返回值，有异常会爆出异常
 * @param $result_data
 * @return string 返回data的值
 */
function get_api_data($result_data){
    $resultdata = json_decode($result_data);
    if ($resultdata->success == "1") {
        return  $resultdata->data;
    } else {
        throw E(\mysoft\helpers\String::jsonEncode($resultdata->data));
    }
}

/**
 * assert以@web代表当前项目的webroot目录，@static代表静态文件服务器（配置在params['static_envs']里面）
 * @param string $jsOrCss
 * @param int $pos 传1代表插入到head标签内，返回null；<link><script>的顺序按照调用asset的顺序先后。
 * @throws \yii\base\InvalidParamException
 */
function _asset($jsOrCss,$pos=false) {
    //Yii::setAlias('@static', \mysoft\pubservice\Conf::getConfig('static_site'));
    //如果@static格式，则从静态资源服务器引用
    if(strpos($jsOrCss, '.js') > 0) {
        return \mysoft\helpers\AssetHelper::registerJsFile($jsOrCss,['position'=>$pos]);
    }
    else if(strpos($jsOrCss, '.css') > 0) {
        return \mysoft\helpers\AssetHelper::registerCssFile($jsOrCss,['position'=>$pos]);
    }
    else {
        throw new \yii\base\InvalidParamException('invalid asset extension :'.$jsOrCss);
    }
}


/**
 * 应用程序配置文件完成时回调。
 * 此回调发生在require并合并完配置文件后。
 * 用于需要在新建application前对config进行最后的检查。
 * 注：此回调和随后的两个回调发生在*end/web/index.php里面，请确认是否在正确的时机执行此回调。
 * @param array $config
 * @author fangl
 */
function _onAppConfigLoaded(&$config) {

    if(empty($config['components']['upload']['rootDirName'])) {
        $config['components']['upload']['rootDirName'] = $config['params']['app_code'];
    }
    
    if(YII_DEBUG) {
        //利用yii_debug参数来决定默认错误提示页面参数
        $config['components']['errorHandler']['errorView'] = '@vendor/mysoft/web/views/errorhandler/error.php';
        $config['components']['errorHandler']['exceptionView'] = '@vendor/mysoft/web/views/errorhandler/exception.php';
    }
}


/**
 * 应用程序对象初始化完成回调。
 * 此回调发生在new Applicataion()之后，$app->run()之前。
 * 用于应用初始化后，需要对程序执行一些补充设置操作。
 * @param yii\web\Application $app
 * @author fangl
 */
function _onAppInited(&$app) {
    
}

/**
 * 应用程序退出回调。
 * 此回调发生在run()结束时。
 * @param int $exitstatus
 * @author fangl
 */
function _onAppExit($exitstatus) {
    
}

function jscfg()
{
    return \mysoft\pubservice\Conf::getConfig('api_site').'/'.I('__orgcode').'/wxqy/jssdk/get-config?app_code='.\Yii::$app->params['app_code'];
}

function jscfg_dd()
{
    return \mysoft\pubservice\Conf::getConfig('api_site').'/'.I('__orgcode').'/ddqy/jssdk/get-config?app_code='.\Yii::$app->params['app_code'];
}

/**
 * 
 * @param string $action index/feedback
 * @return string
 */
function feedbackurl($action='index') {
    $orgcode = I("__orgcode");
    $from = I("__from",cookie('__from@'.$orgcode));
    
    $app_code = \Yii::$app->params['app_code'];
    if($app_code == '0000') {
        $app_code = '';
    }
    if(empty($orgcode)) {
        return \mysoft\pubservice\Conf::getConfig('api_site')."/feedback/feedback/{$action}?__from={$from}&app_code={$app_code}";
    }
    else {
        return \mysoft\pubservice\Conf::getConfig('api_site')."/{$orgcode}/feedback/feedback/{$action}?__from={$from}&app_code={$app_code}";
    }
}