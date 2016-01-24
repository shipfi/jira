<?php
namespace mysoft\user;
use mysoft\base\Exception;
use yii\web\IdentityInterface;

/**
 * 微网站用户身份
 * Class MicroIdentity
 * @package mysoft\user
 */
class MicroIdentity implements IdentityInterface
{
    public $orgcode;
    public $openid;
    public $userId;
    public $erpUserCode;
    public $erpUserGuid;
    public $userName;
    public $position;
    public $tel;
    public $email;
    public $avatar;
    public $sex;
    public $status;
    private  $rolecodes;
    public $userkind;
    
    public static function findIdentity($id)
    {

        \Yii::info($id,__METHOD__);
        $current = \Yii::$app->session->get($id);

        if(!empty($current)){
            $identity = new MicroIdentity();
            $identity->openid = $current['openid'];
            $identity->userId = $current['user_id'];
            $identity->userName = $current['user_name'];
            $identity->position = $current['position'];
            $identity->tel = $current['tel'];
            $identity->email = $current['email'];
            $identity->avatar = $current['avatar'];
            $identity->sex = $current['sex'];
            $identity->status = $current['status'];
            $identity->erpUserCode = $current['erp_user_code'];
            $identity->erpUserGuid = $current['user_guid'];
            //$identity->rolecodes = $current['rolecodes'];
            $identity->userkind = @$current['user_kind'];
            $identity->orgcode = @$current['orgcode'];
            return $identity;
        }
        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {

    }

    public function getId()
    {
        return I("__orgcode")."@@".$this->userId;
    }

    public function getAuthKey()
    {

    }

    public function validateAuthKey($authKey)
    {

    }
    
    public function __get($name) {
        //lazy load rolecodes
        if( $name === 'rolecodes' ) {
            return $this->getUserRoles($this->userId);
        }
        else return $this->$name;
    }
    
    //递归从alldepts中寻找deptId的所有父级id
    function getParentDeptIds($deptId, $alldepts, $deptIds=[], $depth=0) {
        //如果为空，或者递归超过100层，代表已经到顶级节点（递归层级限制防止因为数据结构的问题导致死循环）
        if (empty($deptId) && $depth > 100) {
            return $deptIds;
        }
        else {
            //当前节点增加到父级节点列表中去，递归寻找当前节点的父级节点
            $deptIds[] = $deptId;
            foreach($alldepts as $dept) {
                if( strcmp($dept['dept_id'],$deptId) == 0 ) {
                    return $this->getParentDeptIds($dept['parent_id'], $alldepts, $deptIds, ++$depth);
                }
            }
            return $deptIds;
        }
    }
    
    /**
     * 获取用户的角色数组
     * @param string $user_id
     * @return array [$role_code1,$role_code2]
     * @author fangl
     * @see srvs\setting\PRolesMemberService::getUserRoles
     */
    protected function getUserRoles($user_id) {
    
        //获取所有部门
        $alldepts = DB($this->orgcode)->createCommand('select dept_id,parent_id from p_department')->queryAll();
    
        //获取用户所在的所有部门
        $alluserdepts = DB($this->orgcode)->createCommand('select distinct dept_id from p_user_to_dept where user_id = :UID',[':UID'=>$user_id])->queryAll();
        $user_dept_ids = [];
        foreach($alluserdepts as $dept) {
            $user_dept_ids[] = $dept['dept_id'];
        }
    
        $deptIds = [];
        foreach($user_dept_ids as $user_dept_id) {
            //合并每个部门继承下来的部门id
            $ret = $this->getParentDeptIds($user_dept_id, $alldepts);
            foreach($ret as $v) {
                if(!in_array($v, $deptIds)) {
                    $deptIds[] = $v;
                }
            }
        }
        $deptIds = array_filter($deptIds);
    
        if(!empty($deptIds)) {
            $deptIds = array_map(function($value){ return DB($this->orgcode)->quoteValue($value); }, $deptIds);
            $sql = 'select distinct role_code from p_roles_member where user_id = :UID or dept_id in ( '.implode(',',$deptIds).' )';
        }
        else $sql = 'select distinct role_code from p_roles_member where user_id = :UID';
        //查询用户的所有角色（包括直接指定和通过部门继承下来的）
        $roles = DB($this->orgcode)->createCommand($sql,[':UID'=>$user_id])->queryAll();
    
        $ret = [];
        foreach($roles as $r) {
            $ret[] = $r['role_code'];
        }
    
        return $ret;
    }
}

