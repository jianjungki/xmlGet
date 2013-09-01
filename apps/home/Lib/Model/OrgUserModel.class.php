<?php
class OrgUserModel extends UserModel {
    protected   $tableName  =   'org_info';
    public static $nameHash = array();
    var $uid;
    /**
     * 根据查询条件查询用户
     *
     * @param array|string $map          查询条件
     * @param string       $field          字段
     * @param int          $limit          限制条数
     * @param string       $order          结果排序
     * @param boolean      $is_find_page 是否分页
     * @return array
     */
    public function getUserByMap($map = array(), $field = '*', $limit = '', $order = '', $is_find_page = true) {
        if ($is_find_page) {
            return $this->where($map)->field($field)->order($order)->findPage($limit);
        }else {
            return $this->where($map)->field($field)->order($order)->limit($limit)->findAll();
        }
    }
    /**
     * 获取用户列表
     *
     * @param array|string $map             查询条件
     * @param boolean      $show_dept       是否显示部门信息
     * @param boolean      $show_user_group 是否显示用户组
     * @param string       $field                  字段
     * @param string       $order                  结果排序
     * @param int          $limit           限制条数
     * @return array
     */
    public function getUserList($map = '', $show_dept = false, $show_user_group = false, $field = '*', $order = 'uid ASC', $limit = 30) {
        $res  = $this->where($map)->field($field)->order($order)->findPage($limit);
        $uids = getSubByKey($res['data'], 'uid');
        //部门信息
        if ($show_dept) {
        }
        //用户组
        if ($show_user_group) {
            $temp_user_group = model('UserGroup')->getUserGroupByUid($uids);
            //转换成array($uid => $user_group)的格式
            $user_group = array();
            foreach($temp_user_group as $v) {
                $user_group[$v['uid']][] = $v;
            }
            unset($temp_user_group);
            //将用户组信息添加至结果集
            foreach($res['data'] as $k => $v) {
                $res['data'][$k]['user_group'] = isset($user_group[$v['uid']]) ? $user_group[$v['uid']] : array();
            }
        }
        return $res;
    }
    /**
     * 更新操作
     *
     * @param string $type 操作
     * @return boolean
     */
    function upDate($type){
        return $this->$type();
    }
    /**
     * 更新基本信息
     *
     * @return array
     */
    private function upbase( ){
        $nickname = isset($_POST['nickname'])?t($_POST['nickname']):t($_POST['unitFullName']);
        if(!$nickname){
            $data['message'] = L('nickname_nonull');
            $data['boolen']  = 0;
            return $data;
        }
    
        if( !isLegalUsername($nickname) ){
            $data['message'] = L('nickname_format_error');
            $data['boolen']  = 0;
            return $data;
        }
        if( checkKeyWord($nickname) ){
            $data['message'] = '昵称含有敏感词';
            $data['boolen']  = 0;
            return $data;
        }
        if( M('user')->where("uname='{$nickname}' AND uid!={$this->uid}")->find() ){
            $data['message'] = L('nickname_used');
            $data['boolen']  = 0;
            return $data;
        }
        //更新组织个人信息
        $data['province'] = intval( $_POST['area_province'] );
        $data['uname']    = $nickname;
        $data['city']     = intval( $_POST['area_city'] );
        $data['location'] =  getLocation($data['province'],$data['city']);
        $data['sex']      = intval( $_POST['sex'] );
        M('user')->where("uid={$this->uid}")->data($data)->save();
        //更新组织内部信息
        $linkinfo = D('user')->where("uid={$this->uid}")->find();
        
        $orgdata['location'] = getLocation($data['province'],$data['city']);
        $orgdata['unitFullName'] = $nickname;
        //$orgdata['telephone'] = $_POST['telephone'];
        $orgdata['aboutit'] = $_POST['aboutit'];
        $orgdata['unitShortName'] = $_POST['unitShortName'];
        //$orgdata['username'] = $_POST['username'];
        D('OrgUser', 'home')->where("orgid={$linkinfo['deputyoriid']}")->data($orgdata)->save();
        
        //修改登录用户缓存信息--名称
        $userLoginInfo = S('S_userInfo_'.$this->uid);
        if(!empty($userLoginInfo)) {
            $userLoginInfo['uname'] = $data['uname'];
            S('S_userInfo_'.$this->uid, $userLoginInfo);
        }
        $_SESSION['userInfo'] = D('User', 'home')->find($this->uid);
        $data['message'] = L('update_done');
        $data['boolen']  = 1;
        return $data;
    }
    /**
     * 获取用户基本信息字段
     *
     * @param string $module 字段类别,contact联系的字段、inro基本介绍的字段
     * @return array
     */
    protected function data_field($module = '',$space=false){
       if($space){
            $list = $this->table(C('DB_PREFIX').'user_set')->where("status=1 and showspace=1")->findAll();
        }else{
            $list = $this->table(C('DB_PREFIX').'user_set')->where("status=1")->findAll();
        }
        foreach ($list as $value){
            $data[$value['module']][$value['fieldkey']] = $value['fieldname'];
        }
        return ($module)?$data[$module]:$data;
    }
    
    
    function DelMember($orgid,$uid){
        M('OrgUser')->where("orgid = ".$orgid." AND uid = ".$uid)->delete();
        D('Member','group')->where('gid = '.$gid.' and uid IN ('.$_POST['uid'].')'.$level)->delete();
        return true;
    }
}