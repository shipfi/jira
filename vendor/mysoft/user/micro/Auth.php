<?php
namespace mysoft\user\micro;

/**
 * 鉴权抽象类，所有认证类需要继承自此方法
 * @author fangl
 *
 */
abstract class Auth {
    
    //授权租户的orgcode
    protected $orgcode;
    
    //模拟当前授权dev_account_id
    protected $dev_account_id;

    /**
     * @var \yii\web\Session
     */
    private $session;
    
    /**
     * @var \yii\web\User
     */
    private $webUser;
    
    /**
     * @var \yii\db\Connection
     */
    private $db;
    
    public function __construct($orgcode) {
        $this->orgcode = $orgcode;
    }
    
    /**
     * 获取当前鉴权通过的用户对应中台p_user信息。<br />
     * 默认逻辑是根据子类的鉴权逻辑获得当前用户的身份id; 然后，根据子类实现的userInfoByField对应的where条件获取中台用户
     * @return array
     */
    protected function getAccountUser() {
        \Yii::trace('getAccountUser',__METHOD__);
        return $this->getUserInfoBy($this->userInfoByField(), $this->getAccountId());
    }
    
    /**
     * 根据字段名称获取中台用户信息
     * @param string $field
     * @param string $account_id
     * @return Ambigous <\yii\db\array, \yii\db\boolean>
     */
    private function getUserInfoBy($field,$account_id) {
        \Yii::trace('getUserInfoBy',__METHOD__);
        \Yii::info('field='.$field, __METHOD__);
        \Yii::info('account_id='.$account_id, __METHOD__);
        //根据$account_id，查询业务信息
        $sql = "SELECT p_user.openid,p_user.user_id,p_user.user_name,p_user.position,p_user.tel,p_user.email,p_user.avatar,p_user.sex,p_user.`status`,
        erp_user.user_code AS erp_user_code,erp_user.user_guid,p_user.session_id,erp_user.user_kind
        FROM p_user
        left join p_user_to_erp on p_user.user_id = p_user_to_erp.user_id
        left join erp_user on erp_user.user_guid = p_user_to_erp.user_guid
        WHERE {$field} = :ACCOUNT_ID";
        $user = $this->getDb()->createCommand($sql, [
            ":ACCOUNT_ID" => $account_id
        ])->queryOne();
        return $user;
    }
    
    /**
     * 获取中台用户信息的where条件，用于提供给getUserInfoBy函数的第一个参数。
     * 如果子类是类似getAccountUser的逻辑获取中台用户的，需实现此方法，返回一个字符串标准getUserInfoBy中的where条件
     * @return string
     * @throws AuthException
     */
    protected function userInfoByField() {
        throw new AuthException('子类必须重写userInfoByField方法或者getAccountUser方法');
    }
  
    /**
     * 轻应用登录业务逻辑
     */
    public function login() {
        $user = $this->getAccountUser();
        
        if(empty($user)) {
            if($this instanceof WxAuth) {
                $this->clearCookie();
                throw new AuthException('当前用户身份未能识别');
            }
            else {
                $this->clearCookie();
                throw new AuthException('登录状态失效，请重新登录。');
            }
        }
        
        if($user['status'] == 1) {
            throw new AuthException('账号已经被禁用');
        }
        
        if(empty($user['erp_user_code'])){
            throw new AuthException("账号".$this->getAccountId()."未与erp用户进行绑定。");
        }

        $identity = new \mysoft\user\MicroIdentity();
        $identity->openid = $user['openid'];
        $identity->userId = $user['user_id'];
        $identity->userName = $user['user_name'];
        $identity->position = $user['position'];
        $identity->tel = $user['tel'];
        $identity->email = $user['email'];
        $identity->avatar = $user['avatar'];
        $identity->sex = $user['sex'];
        $identity->status = $user['status'];
        $identity->erpUserCode = $user['erp_user_code'];
        $identity->erpUserGuid=$user['user_guid'];
        $identity->userkind = $user['user_kind'];
        $identity->orgcode = $this->orgcode;

        $user['orgcode'] = $this->orgcode;
        $this->getSession()->set($this->orgcode.'@@'.$user['user_id'], $user);
        $this->updateSessionId($user['user_id'], $user['session_id']);
        
        return $this->getWebUser()->login($identity, 0);
    }
    
    /**
     * 
     * @param int $user_id
     * @param string $session_id
     * @return boolean
     */
    private function updateSessionId($user_id, $session_id) {
        $session_id = is_string($session_id)?json_decode($session_id,true):(is_array($session_id)?$session_id:[]);
        if(empty($session_id) || !is_array($session_id)) {
            $session_id = [];
        }
        
        $session = $this->getSession();
        
        $sid_max_num = YII_ENV == 'dev'?20:3;
        //记录的session_id的列表大于max_num时（即同时有max_num个用户以一个中台用户身份登录时），启动session_id回收机制
        if(count($session_id) >= $sid_max_num) {
            //首先移除已经为空的的session信息，merge用于重排下标
            $session_id = array_merge(
                array_filter($session_id,
                    function($sid) use($session) {
                        $info = $session->readSession($sid);
                        if(empty($info)) {
                            return false;
                        }
                        else return true;
                    }));
                    //如果仍大于max_num，最早的那个过期
                    if(count($session_id) >= $sid_max_num) {
                        $sid = array_shift($session_id);
                        $session->destroySession($sid);
                    }
        }
        
        $session_id[] = $session->getId();
        $sql = 'update p_user set session_id = :SID where user_id = :UID';
        $this->getDb($this->orgcode)
        ->createCommand($sql,[':SID'=>json_encode($session_id),':UID'=>$user_id])
        ->execute();
    }
    
    /**
     * 获取当前用户ID，如果是开发模式下，取dev_account_id。
     */
    private function getAccountId() {
        if($this->dev_account_id) {
            return $this->dev_account_id;
        }
        else return $this->getAuthAccountId();
    }
    
    /**
     * 鉴权获取轻应用用户身份ID，由子类根据具体的业务逻辑实现
     */
    abstract protected function getAuthAccountId();
    
    /**
     * 模拟轻应用用户身份ID，用于开发模式
     * @param string $account_id
     */
    public function setDevAccountId($dev_account_id) {
        $this->dev_account_id = $dev_account_id;
    }
    
    private function getSession() {
        return $this->session?$this->session:\Yii::$app->session;
    }
    
    public function setSession(\yii\web\Session $session) {
        $this->session = $session;
    }
    
    private function getWebUser() {
        return $this->webUser?$this->webUser:\Yii::$app->user;
    }
    
    public function setWebUser(\yii\web\User $webuser) {
        $this->webUser = $webuser;
    }
    
    private function getDb() {
        return $this->db?$this->db:DB($this->orgcode);
    }
    
    public function setDb(\yii\db\Connection $db) {
        $this->db = $db;
    }

    public function clearCookie() {
        foreach ($_COOKIE as $k => $value) {
            setcookie($k);
        }
    }
}