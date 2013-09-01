<?php
/**
 * OrgAccountAction class
 *
 * @package default
 * @author  jianjungki
 */
class OrgAccountAction extends AccountAction {
    var $member;
    var $pOrgUser;
    var $orginfo;
    var $statement;
    function _initialize(){    
        $this->pOrgUser = D('OrgUserProfile');
        $this->pOrgUser->uid = $this->mid;
        $this->member = D('OrgUserlink');
        $this->statement = D('Statement');
        // 组织信息
        $this->orginfo = D('OrgUser')->where("orgid = {$this->orgid}")->find();
        if (!$this->orgid) {
            $this->error('orgid 错误');
        }
        
        //dump( $this->orgid);
        $menu[] = array( 'act' => 'orgindex',      'name' => L('base_info') );
        $menu[] = array( 'act' => 'avatar',     'name' => L('org_iconimg') );
        $menu[] = array( 'act' => 'privacy',     'name' => L('private_setting') );
        $menu[] = array( 'act' => 'domain',     'name' => L('self_domain') );
        $menu[] = array( 'act' => 'security',     'name' => L('account_safe') );
        //$menu[] = array( 'act' => 'credit',   'name' => L('integral_rule') );
        $menu[] = array( 'act' => 'statement',     'name' => L('org_statement') );
        $menu[] = array( 'act' => 'managemem',   'name' => L('member_manage') );
        
        //$menu[] = array( 'act' => 'request',       'name' => L('set_request') );
        //$menu[] = array( 'act' => 'manageorg',     'name' => L('org_manage') );
        $menu[] = array( 'act' => 'signset',     'name' => L('org_signset') );
        // $menu[] = array( 'act' => 'verifyinfo',     'name' => L('verify_info') );
        
        //dump($orginfo);
        $this->assign('orginfo',$orginfo);
        $this->assign('orgid', $this->orgid);
        $this->assign('accountmenu', $menu);
    }
   /**
     * orgIndex function
     * 2012-09-11 
     * 组织信息首页
     * @return void
     * @author  jianjungki
     */
    public function orgIndex() {
        $data['OrguserInfo']      = $this->pOrgUser->getUserInfo();
        $data['userTag']          = D('UserTag')->getUserTagList($this->mid);
        $data['userFavTag']       = D('UserTag')->getFavTageList($this->mid);
        
        $attach_id = $data['OrguserInfo']['Orgdetail']['fileId'];
        $attach = D('attach')->where('id ='.$attach_id)->find();
        $data['OrguserInfo']['attachment'] = $attach['name'];
        $this->assign( $data );
        $this->setTitle(L('setting').' - '.'组织资料');
        
        $verinfo = D("VerifiedLog")->GetLastLog($this->mid);
        $this->assign("veridata",$verinfo);
        $veriset = D("UserVerified")->where("uid = ".$this->mid)->find();
        $this->assign("verified",$veriset);
        $this->display();
    }
    /**
     * Orgupdate function
     * 2012-09-11 updated on 2012-11-17
     * 组织信息更新
     * @return void
     * @author  jianjungki
     */
    function Orgupdate(){
        S('S_userInfo_'.$_SESSION['userInfo']['uid'],null);
		
		//检查禁止注册的用户昵称
		if ($_REQUEST['dotype']=='upbase') {
	        $nickname = $_REQUEST['unitFullName'];
	        $audit = model('Xdata')->lget('audit');
	        if($audit['banuid']==1){
	            $bannedunames = $audit['bannedunames'];
	            if(!empty($bannedunames)){
	                $bannedunames = explode('|',$bannedunames);
	                if(in_array($nickname,$bannedunames)){
	                    exit(json_encode(array('message'=>'这个昵称禁止注册','boolen'=>0)));
	                }
	            }
	        }
		}
        if(!$_POST['content']){
            exit(json_encode(array('message'=>'认证资料不能为空','boolen'=>0)));
        }
        //认证信息合并内容
            if(isset($_POST['attach'])){
                $data['attachment']=$_POST['attach']['0'];
            }
			
            $data['realname'] = preg_match('/^[\x{2e80}-\x{9fff}]+$|^[a-zA-Z\.·]+$/u', $_POST['username']) ? $_POST['username'] : '';
            $data['phone']    = $_POST['telephone'];
            $data['reason']   = "组织认证";
            $data['info']   = $_POST['content'];
            $data['veritype']   = "组织认证";
            $data['verified']   = "0";
            $data['uid']   = $this->mid;
            
             M('user_verified')->where("uid = ".$data['uid'])->setField("verified","0");
        //获取基本信息
            $data['unitFullName'] = $_POST['unitFullName'];
            $data['uid'] = $this->mid;
            $data['unitShortName'] = $_POST['unitShortName'];
            $data['category'] = $_POST['category'];
            $data['website'] = $_POST['website'];
            $data['username'] = $_POST['username'];
            $data['province'] = intval($_POST['area_province']);
            $data['city'] = intval($_POST['area_city']);
            $data['telephone'] = $_POST['telephone'];
            $data['veritime'] = time();
            $data['verified'] = '0';
            $data['veritype'] = h("组织认证");
        $res = D("VerifiedLog")->SaveLog($data);
        if($res){
            $data['message'] = '更新完成';
            $data['boolen']  = 1;
			$data['setno']  = 1;
        }
        exit( json_encode($data) );
    }
    
    /**
     * InfoUpdate function
     * 2012-09-11 updated on 2012-11-17
     * 组织信息更新
     * @return void
     * @author  jianjungki
     */
    function InfoUpdate(){
        $res = $this->pOrgUser->where("orgid = ".$_REQUEST['orgid'])->setField("aboutit",$_REQUEST['aboutit']);
		
		//更新组织信息到(圈子中的)组织
		$gres = D('Group','group')->where("deputyorgid = " . $_REQUEST['orgid'] . " AND uid =" . $this->mid)->setField("intro",$_REQUEST['aboutit']);
        
        if($res&&$gres){
        	$text['boolen'] = 1;
        	$text['message'] = "更新成功";
        }else{
        	$text['boolen'] = 0;
        	$text['message'] = "更新失败";
        }
        
        echo json_encode($text);
    }
    /**
     * managemem function
     * 成员管理函数
     * 主要入口
     * @return void
     * @author  jianjungki
     */
    function managemem() {
        
        $type = (isset($_GET['type']) && in_array($_GET['type'], array('manage','apply')))?$_GET['type']:'';
        if ('' == $type || 'apply' == $type) {
             $memberlist = $this->member->where("orgid={$this->orgid} AND isblack = 0 AND request = 2 AND level = 3")->findPage();
            $type = 'apply';
        }
        if ('manage' == $type || (!$memberlist['data'] && 'apply' != $_GET['type'])) {
            $memberlist = $this->member->where("orgid={$this->orgid} AND isblack = 0 AND request = 0 AND level>0")->order('level ASC')->findPage();
            $type = 'manage';
        }
       
        
        /*
         * 缓存当前页用户信息
         */
        $ids = getSubBeKeyArray($memberlist['data'], 'uid');
        D('User', 'home')->setUserObjectCache($ids['uid']);

        $this->assign('memberInfo',$memberlist);
        //$this->assign('iscreater',iscreater($this->mid,$this->orgid));

        $this->assign('type', $type);

        if('apply' == $type) {
            $this->display('memberapply');
            exit;
        }else{
            $this->display();
        }
    }
    
    protected function _getSearchKey($key_name = 'k',$prefix="group_search") {
            $key = '';
            // 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
            if ( isset($_REQUEST[$key_name]) && !empty($_REQUEST[$key_name]) ) {
                if($_GET[$key_name]){
                    $key = html_entity_decode( urldecode($_GET[$key_name]) ,ENT_QUOTES);
                }elseif($_POST[$key_name]){
                    $key = $_POST[$key_name];
                }
                // 关键字不能超过30个字符
                if ( mb_strlen($key, 'UTF8') > 30 )
                    $key = mb_substr($key, 0, 30, 'UTF8');
                $_SESSION[$prefix.'_' . $key_name] = serialize( $key );
            }else if ( is_numeric($_GET[C('VAR_PAGE')]) ) {
                $key = unserialize( $_SESSION[$prefix.'_' . $key_name] );
            } else {
        unset($_SESSION[$prefix.'_' . $key_name]);
      }
            $this->assign('search_key', h(t($key)));
            return trim($key);
    }
    /**
     * statement function
     * 2012-09-12
     * 公告设置方法
     * @return void
     * @author  jianjungki
     */
    public function statement(){
        
        if (isset($_POST['editsubmit'])) {
            $groupinfo['postcontent'] = t(getShort($_POST['postcontent'], 200));
            $groupinfo['postdate'] = date('Y-m-d');
            $groupinfo['postmanId'] = $this->mid;
            $groupinfo['postman'] = $this->user['uname'];
            $groupinfo['orgid'] = $this->orgid;
            //$res = $this->group->where('id='.$this->gid)->save($groupinfo);
            
            //更新组织信息到(圈子中的)组织
			//D('Group','group')->where("deputyorgid = " . $this->orgid . " AND uid =" . $this->mid)->setField("announce",t(getShort($_POST['postcontent'], 200)));
            
            if($_REQUEST['op']=='edit'){
                $res = $this->statement->where('id='.$_GET['statid'])->save($groupinfo);
                if ($res !== false) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
            else{
                $res = $this->statement->add($groupinfo);
                if ($res !== false) {
                    $this->success('添加成功');
                } else {
                    $this->error('添加失败');
                }
             }
        }
        $stateinfo = $this->statement->where("postmanId = ".$this->mid)->findPage();
        $this->assign("stateinfo",$stateinfo);
        $this->display();
    }

    /**
     * statement function
     * 2012-09-12
     * 公告处理方法（删改）
     * @return void
     * @author  jianjungki
     */
    public function statementact(){
        dump($_REQUEST['op']);
        if(!isset($_REQUEST['op']) || !in_array($_REQUEST['op'],array('edit','del','alldel'))) redirect(U('home/orgAccount/statement'));
        //dump($_POST);
        switch ($_REQUEST['op']) {
            case 'edit':
                $stateitem = $this->statement->where("orgid =".$_REQUEST['orgid']." AND id =".$_REQUEST['statid'])->find();
                $this->assign("stateitem",$stateitem);
                $stateinfo = $this->statement->where("postmanId = ".$this->mid)->findPage();
                $this->assign("stateinfo",$stateinfo);
                $this->display("statement");
                return;
                break;
                
            case 'del':
                $state = $this->statement->where("orgid =".$_POST['orgid']." AND id =".$_POST['statid'])->delete();
                if($state)
                    $this->success("删除公告成功");
                else
                    $this->error("删除公告失败");
                break;
                
            case 'alldel':
                $state = $this->statement->where("orgid =".$_POST['orgid']." AND id IN (".$_POST['statid'].")")->delete();
                //dump("orgid =".$_POST['orgid']." AND id IN (".$_POST['statid'].")");
                if($state)
                    $this->success("删除公告成功");
                else
                    $this->error("删除公告失败");
                break;
        }
        header('Location:'.$_SERVER['HTTP_REFERER']);
    }
    /**
     * request function
     * 2012-09-12 
     * 请求处理方法，弃用
     * @return void
     * @author  jianjungki
     */
   /* public function request(){
        $this->display();
    }
    */
    /**
     * manageorg function
     * 2012-09-12
     * 组织管理方法，待用
     * @return void
     * @author  jianjungki
     */
     /*
    public function manageorg(){
        $this->display();
    }
    */
    /**
     * signset function
     * 2012-09-12
     * @return void
     * @author  jianjungki
     */
    public function signset(){
        $data['userInfo']         = $this->pOrgUser->getUserInfo();
        $data['userTag']          = D('UserTag')->getUserTagList($this->mid);
        $data['userFavTag']       = D('UserTag')->getFavTageList($this->mid);
        $this->assign( $data );
        $this->display();
    }
    
    /**
     * verifyinfo function
     * 2012-09-12
     * 认证信息页面
     * @return void
     * @author  jianjungki
     */
    public function verifyinfo(){
        if(isset($_POST['attach'])){
            $orginfo['username'] = $_POST['username'];
            $orginfo['telephone'] = $_POST['telephone'];
            $orginfo['content'] = $_POST['content'];
            $orginfo['fileId']=$_POST['attach']['0'];
            $res1 = $this->pOrgUser->where("orgid = {$this->orgid}")->save($orginfo);
            
            $data['attachment']=$_POST['attach']['0'];
            $data['realname'] = preg_match('/^[\x{2e80}-\x{9fff}]+$|^[a-zA-Z\.·]+$/u', $_POST['username']) ? $_POST['username'] : '';
            $data['phone']    = $_POST['telephone'];
            $data['reason']   = $_POST['content'];
            $data['verified'] = 0;
            $data['type'] = 1;
            
            if(!empty($_POST['id'])){
                $res2 = M('user_verified')->where("uid = {$this->mid}")->save($data);
            }
            else {
                $data['uid'] = $this->mid;
                $res2 = M('user_verified')->add($data);
            }
            if($res1&&$res2){
                $this->success("提交认证信息成功");
            }else{
                $this->error("提交认证信息失败，请重新提交");
            }
        }
        $verinfo = M('user_verified')->where("uid = {$this->mid}")->find();
        $this->assign("verinfo",$verinfo);
        $this->display();
    }
    
    //操作：设置成管理员，降级成为普通会员，剔除会员，允许成为会员，加会员成为黑名单
    public function memberaction() {
        $batch = false;
        $uidArr = explode(',', $_POST['uid']);
        if(is_array($uidArr)) {
            $batch = true;
        }
        $gid = D('Group','group')->where("deputyorgid = ".$this->orgid)->getField("id");
        if(!isset($_POST['op']) || !in_array($_POST['op'],array('admin','normal','out','allow','black'))) exit();
        //dump(iscreater($this->mid, $this->orgid));
        switch ($_POST['op'])
        {
            /*case 'admin':  // 设置成管理员
                if (!iscreater($this->mid,$this->orgid)) {
                    $this->error('创建者才有的权限');  // 创建者才可以进行此操作
                }
                if($batch) {
                    $uidStrLog = array();
                    foreach($uidArr as $val) {
                        $uidInfo = getUserSpace($val, 'fn', '_blank', '@' . getUserName($val));
                        array_push($uidStrLog, $uidInfo);
                    }
                    $uidStr = implode(',', $uidStrLog);
                    $content = '将用户 '.$uidStr.'提升为管理员 ';
                    $res = D('Member')->where('orgid=' . $this->orgid . ' AND uid IN ('.$_POST['uid'].') AND level<>1')->setField('level', 2);   //3 普通用户
                } else {
                    $content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '提升为管理员 ';
                    $res = D('Member')->where('orgid=' . $this->orgid . ' AND uid=' . $this->uid . ' AND level<>1')->setField('level', 2);   //3 普通用户
                }
                break;
            case 'normal':   // 降级成为普通会员
                if (!iscreater($this->mid,$this->orgid)) {
                    $this->error('创建者才有的权限');  // 创建者才可以进行此操作
                }
                $content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '降为普通会员 ';
                $res = D('Member')->where('orgid=' . $this->orgid . ' AND uid=' . $this->uid . ' AND level=2')->setField('level', 3);   //3 普通用户
                break;*/
            case 'out':     // 剔除会员
                if (iscreater($this->mid, $this->orgid)) {
                    $level = ' AND level<>1';
                } else {
                    $level = ' AND level<>1 AND level<>2';
                }
                if($batch) {
                    $current_level = D('OrgUserlink')->field('uid, level')->where('orgid = '.$this->orgid.' AND uid IN ('.$_POST['uid'].')'.$level)->findAll();
                    D('Member','group')->where('gid = '.$gid.' and uid IN ('.$_POST['uid'].')'.$level)->delete();
                    $res = D('OrgUserlink')->where('orgid='.$this->orgid.' AND uid IN ('.$_POST['uid'].')'.$level)->delete();
                    if($res) {
                        $count = count($current_level);
                        $uidStrLog = array();
                        foreach($current_level as $value) {
                            $uidInfo = getUserSpace($value['uid'], 'fn', '_blank', '@' . getUserName($value['uid']));
                            array_push($uidStrLog, $uidInfo);
                            if($value['level'] > 0) {
                                D('Group')->setDec('membercount', 'id=' . $this->orgid);
                                X('Credit')->setUserCredit($value['uid'], 'quit_group');
                            }
                        }
                        $uidStr = implode(',', $uidStrLog);
                        $content = '将用户 '.$uidStr. '踢出组织 ';
                    }
                } else {
                    $current_level = D('OrgUserlink')->getField('level', 'orgid=' . $this->orgid . ' AND uid=' . $this->uid . $level);
                    D('Member','group')->where('gid = '.$gid.' and uid = '.$_POST['uid'].$level)->delete();
                    $res = D('OrgUserlink')->where('orgid=' . $this->orgid . ' AND uid=' . $this->uid . $level)->delete();   //剔除用户
                    if ($res) {
                        $content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '踢出组织 ';
                        // 被拒绝加入不扣积分
                        if (intval($current_level) > 0) {
                            D('Group')->setDec('membercount', 'id=' . $this->orgid);     //用户数量减少1
                            X('Credit')->setUserCredit($this->uid, 'quit_group');
                        }
                    }
                }
                break;
            case 'allow':   // 批准成为会员
                $content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '批准成为会员 ';
                $res = D('OrgUserlink')->where('orgid=' . $this->orgid . ' AND uid=' . $_POST['uid'] . ' AND level=3')->setField(array('request','ctime'), array(0,time()));   //level级别由0 变成2
                if ($res) {
                    D('Group')->setInc('membercount', 'id=' . $this->orgid); //增加一个成员
                    X('Credit')->setUserCredit($this->uid, 'join_group');
                }
                break;
            case 'black':   // 加入到黑名单
                $content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '加入组织黑名单 ';
                $res = D('OrgUserlink')->where('orgid=' . $this->orgid . ' AND uid=' . $this->uid )->setField('isblack', 1);   //level级别由0 变成 3
                if ($res) {
                    D('Group')->setDec('membercount', 'id=' . $this->orgid); //增加一个成员
                    X('Credit')->setUserCredit($this->uid, 'black_group');
                }
                break;
        }

        /*if ($res) {
            D('Log')->writeLog($this->orgid, $this->mid, $content, 'member');
        }*/

        header('Location:'.$_SERVER['HTTP_REFERER']);
        //$this->redirect('/Manage/membermanage',array('orgid'=>$this->orgid));
    }

    // 组织创建的邀请
    public function create()
    {
        $from  = isset($_GET['from']) ? t($_GET['from']) : '';
        if($_POST['sendsubmit']) {
            $toUserIds = explode(",",$_POST["fri_ids"]);
            if(empty($toUserIds)){
                $this->error('请选择您的好友！');exit();
            }
            foreach ($toUserIds as $k=>$v) {
                if (!$v) continue;
                if (D('OrgUserlink')->where("orgid={$this->orgid} AND uid={$v}")->count() > 0) {
                    unset($toUserIds[$k]);
                    continue;
                }
                /*$isadmin  =
                if ($isadmin) {
                    $invite_verify_data['gid'] = $this->gid;
                    $invite_verify_data['uid'] = $v;
                    M('group_invite_verify')->add($invite_verify_data);
                }*/
            }
            
            $message_data['title']   = "邀您加入圈子 {$this->orginfo['unitFullName']}";
            $url = '<a href="' . U('home/Space/index', array('uid'=>$this->mid)).'" target="_blank">去看看</a>';
            $message_data['content'] = "你好，诚邀您加入组织“{$this->orginfo['unitFullName']}” ，点击   " . $url . '进入。';
            foreach ($toUserIds as $t_u_k => $t_u_v) {
                $message_data['to']      = $t_u_v;
                $res = model('Message')->postMessage($message_data,  $this->mid);
                if (!$res) {
                    unset($toUserIds[$t_u_k]);
                }
                //添加成员信息
                $data['uid'] = $t_u_v;
                $data['orgid'] = $this->orginfo['orgid'];
                $data['request'] = 1;
                $data['requestdate'] = time();
                $data['isaddresser'] = 0;
                $data['level'] = 3;
                M('OrgUser')->data($data)->add();
                
            }

            if (count($toUserIds) > 0) {
                $this->success("成功发送" .count($toUserIds) ."份邀请");
                //echo count($toUserIds);
            } else {
                $this->error('邀请失败,您的好友可能已经加入了该组织！');
                //echo -1;
            }
            exit;
        }
        $this->assign('from',$from);
        $this->display();
    }

    
}
?>