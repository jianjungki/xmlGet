<?php
class PublicAction extends Action{

	public function code(){
		if (md5(strtoupper($_POST['verify'])) == $_SESSION['verify']) {
			echo 1;
		}else{
			echo 0;
		}
	}
	
	public function adminlogin() {
		if ( service('Passport')->isLoggedAdmin() ) {
			redirect(U('admin/Index/index'));
		}

		$this->display();
	}
	
	//单点登录到天工社区
	public function singLogin(){
		
		if(empty($_GET)){
			$this->error('链接为空');
		}
		
		$account = $_GET['account'];
		$type = $_GET['type'];
		$checkData = $_GET['checkData'];
		$name = $_GET['name'];
		$mai = $_GET['mai'];	
		$sex = $_GET['sex'];
		$time = $_GET['dateData'];
		
		$ip = '010001030165';
		
		if(empty($type)){
			$this->error('类型为空，请检查信息');
			redirect(U('home/User/index'));
		}
		
		if(empty($account))
			$this->error('链接信息，没有帐号');
		
		 if(empty($checkData))
			$this->error('链接信息，验证信息为空');
		 
		
		 if(empty($time) || ($time == 0))
			$this->error('时间为空，请重新链接'); 
		
		$startdate = $this->formatTime($time);
		$enddate = date("Y-m-d H:i:s",time());

		$minute=floor((strtotime($enddate)-strtotime($startdate))%86400/60);
		
	    //判断是否超时
		if($minute >10 )
			$this->error('超时，请重新链接');

		$user = M('login')->where("type_uid='". $account ."'")->find();
		
		/* $sql = "select uid from ts_user where uid='". $uid ."'";
		$Model = M();
		$uid = ($Model->query($sql)); */
		//$uids = $Password[0]['uid'];
		
		$uids = $user["uid"];
		
		//是否存在用户
		if(empty($uids)){
			
			//$this->errorjump('没有此帐号，请先注册',U('home/Public/register',array('type'=>$type)));
			
			//$this->errorjump('没有此帐号，请先注册',U('home/Public/TOA'.'&type='.$type.'&account='.$account.'&uname='.$name.'&addon=Login&hook=home_toa_show'));
			
			redirect(U('home/Public/TOA'.'&type='.$type.'&account='.$account.'&name='.$name.'&mai='.$mai));
			
		}
		

			$mip = MD5($ip);
			$mcheckData = MD5($mip.$account);
	
			//允许登录
			if($checkData == $mcheckData){

					$sql = "select password from ts_user where uid='". $uids ."'";
					$Model = M();
					$Password = ($Model->query($sql));
					$MPassword = $Password[0]['password'];
					
					//$user = M('ts_user')->where("uid='". $uids ."'")->find();
	
					//验证密码
					$result = service('Passport')->loginTLocal($uids,$MPassword,intval($_POST['remember']));
					$lastError = service('Passport')->getLastError();
                    
                 
					
					//检查是否激活
					if (!$result && $lastError =='用户未激活') {
						$this->assign('jumpUrl',U('home/public/login'));
						$this->error('该用户尚未激活，请更换帐号或激活帐号！');
						exit;
					}
					
					Addons::hook('public_after_dologin',$result);
					
					
					if($result) {
						if(UC_SYNC && $result['reg_from_ucenter']){
							//从UCenter导入ThinkSNS，跳转至帐号修改页
							$refer_url = U('home/Public/userinfo');
						}elseif ( $_SESSION['refer_url'] != '' ) {
							//跳转至登录前输入的url
							$refer_url	=	$_SESSION['refer_url'];
							unset($_SESSION['refer_url']);
						}else {
							$refer_url = U('home/User/index');
						}
						$this->assign('jumpUrl',$refer_url);
						$this->assign('waitSecond',5);
						$this->success($username.L('login_success').$result['login']);
					}else {
						$this->error('登录失败'.$lastError);
					}

			}else {
			     $this->error('链接错误，请检查信息');
			     exit();
			}
		
		
	}
	
	//OA的应用接口
	public function appOA(){
		
		if(empty($_GET)){
			$this->error('链接为空');
		}
		
		$account = $_GET['account'];
		$type = $_GET['type'];
		$checkData = $_GET['checkData'];
		$name = $_GET['name'];
		$mai = $_GET['mai'];	
		$sex = $_GET['sex'];
		$time = $_GET['dateData'];
		$oa = $_GET['oa'];
		$ip = '010001030165';
		
		if(empty($type)){
			$this->error('类型为空，请检查信息');
			redirect(U('home/User/index'));
		}
		
		//判断帐号信息
		if(empty($account))
			$this->error('链接信息，没有帐号');
		
		if(empty($checkData))
			$this->error('链接信息，验证信息为空');
		
		//OA任务单地址
		if(empty($oa)){
				
			$this->error('链接地址有误，请检查链接oa信息');
		}
		 
		
		if(empty($time) || ($time == 0))
			$this->error('时间为空，请重新链接');
		
		$startdate = $this->formatTime($time);
		$enddate = date("Y-m-d H:i:s",time());

		$minute=floor((strtotime($enddate)-strtotime($startdate))%86400/60);
		
	    //判断是否超时
		if($minute >10 )
			$this->error('超时，请重新链接');

		$user = M('login')->where("type_uid='". $account ."'")->find();
		

		$uids = $user["uid"];
	
		//如果是空则日志记录
		if(empty($uids)){
			
			
			
		}else
		{
			$mip = MD5($ip);
			$mcheckData = MD5($mip.$account);
			
			//验证通过
			if($checkData == $mcheckData){
				
				$data['typename'] = 'toa';
				$data['touid'] = $uids;
				$data['myml'] = $oa;
				M('myop_myinvite')->add($data);
				
			}else{
				
				$this->error('链接错误，请检查信息');
				exit();
			}
			
			
		}
		
		
	}
	
	//时间格式转化
	public function formatTime($time){
		
		$year = substr($time,0,4);
		$month = substr($time,4,2);
		$day = substr($time,6,2);
		$hour = substr($time,8,2);
		$minite = substr($time,10,2);
		
		return $year.'-'.$month.'-'.$day.' '.$hour.':'.$minite.':'.'00';
	
	}
	
	
	
	//单点登录到OA
	public function singLoginOA(){
	    
		
		//参数  帐号  时间  加密 
		$ip = '010001030165';
		$date =  date("YmdHi",time());  //时间
		
	    $sql = "select type_uid from ts_login where uid='". $this->mid ."'";
	    $Model = M();
	    $Account = ($Model->query($sql));
	    $Accounts = $Account[0]['type_uid'];  //帐号
	    $mip = MD5($ip);
	    $checkData =  MD5($mip.$Accounts);  //加密后的验证
	    
				
		//跳转
		redirect('http://10.1.30.140:8888/tgSingleLoginAction.struts?actionType=login'.'&type=toa'.'&dateData='.$date.'&account='.$Accounts.'&checkData='.$checkData);

	}
	
	
	/**
	 +----------------------------------------------------------
	 * 默认跳转操作 支持错误导向和正确跳转
	 * 调用模板显示 默认为public目录下面的success页面
	 * 提示页面为可配置 支持模板标签
	 +----------------------------------------------------------
	 * @param string $message 提示信息
	 * @param Boolean $status 状态
	 * @param Boolean $ajax 是否为Ajax方式
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return void
	 +----------------------------------------------------------
	 */
	private function errorjump($message,$url,$status=0,$ajax=false)
	{
		// 跳转时不展示广告
		global $ts;
		unset($ts['ad']);
	
		// 判断是否为AJAX返回
		if($ajax || $this->isAjax()) $this->ajaxReturn('',$message,$status);
		// 提示标题
		$this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
		//如果设置了关闭窗口，则提示完毕后自动关闭窗口
		if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
		$this->assign('status',$status);   // 状态
		$this->assign('message',$message);// 提示信息
		//保证输出不受静态缓存影响
		C('HTML_CACHE_ON',false);
		
		//发生错误时候默认停留3秒
		if(!$this->get('waitSecond'))    $this->assign('waitSecond',"3");
		// 默认发生错误的话跳转
		if(!$this->get('jumpUrl')) $this->assign('jumpUrl',$url);
		//sociax:2010-1-21
		//$this->display(C('TMPL_ACTION_ERROR'));
		
		$this->display(THEME_PATH.'&success');
		
		if(C('LOG_RECORD')) Log::save();
		// 中止执行  避免出错后继续执行
		exit ;
	}
	
	/* // 有不存在的ACTION操作的时候执行
	protected function _empty() {
		$this->display('addons');
	} */
	
	public function TOA(){
		
		/* dump(mb_convert_encoding($_GET['name'], "UTF-8", "GBK"));
		return; */
		$this->assign('uname',$_GET['name']);
		$this->assign('mai',$_GET['mai']);
		$this->assign('account',$_GET['account']);
		$this->display("loginOA");
	}
	

	public function doAdminLogin() {
		// 检查验证码
		if ( md5(strtoupper($_POST['verify'])) != $_SESSION['verify'] ) {
			$this->error(L('error_security_code'));
		}

		// 数据检查
		if ( empty($_POST['password']) ) {
			$this->error(L('password_notnull'));
		}

		// 检查帐号/密码
		$is_logged = false;
		if (isset($_POST['email'])) {
			$is_logged = service('Passport')->loginAdmin($_POST['email'], $_POST['password']);
		}else if ( $this->mid > 0 ) {
			$is_logged = service('Passport')->loginAdmin($this->mid, $_POST['password']);
		}else {
			$this->error(L('parameter_error'));
		}

		// 提示消息不显示头部
		$this->assign('isAdmin','1');

		if ($is_logged) {
			$this->assign('jumpUrl', U('admin/Index/index'));
			$this->success(L('login_success'));
		}else {
			$this->assign('jumpUrl', U('home/Public/adminlogin'));
			$this->error(service('Passport')->getLastError());
		}
	}

	public function login()
	{

		if (service('Passport')->isLogged())
			redirect(U('home/User/index'));

		unset($_SESSION['sina'], $_SESSION['key'], $_SESSION['douban'], $_SESSION['qq'],$_SESSION['open_platform_type']);

		//验证码
		$opt_verify = $this->_isVerifyOn('login');
		if ($opt_verify) {
			$this->assign('login_verify_on', $opt_verify);
		}

		$data['email'] = t($_REQUEST['email']);
		$data['uname'] = t($_REQUEST['uname']);
		$data['uid']   = t($_REQUEST['uid']);
		Addons::hook('public_before_login',array(&$data));
		$this->setTitle(L('login'));
		$this->display();
    }
    /******弹出窗*******/
    public function tofriends(){
        $this->display();
    }
    
    public function toguests(){
        $this->assign("topicid",$_GET['topicsId']);
    	$this->display();
    }
    
    public function tohosts(){
        $this->assign("topicid",$_GET['topicsId']);
        $this->display();
    } 
    
     public function setrecommend(){
        $this->assign("topicid",$_GET['topicsId']);
        $this->display();
    } 
    public function toOrgfriends(){
        $this->display();
    }
    public function toGroup(){
        $this->display();
    }
    /*******弹出窗******/
	public function bindaccount() {
		if ( ! in_array($_POST['type'], array('douban','sina','qq')) ) {
			$this->error(L('parameter_error'));
		}

		$psd  = ($_POST['passwd']) ? $_POST['passwd'] : true;
		$type = $_POST['type'];

		if ( $user = service('Passport')->getLocalUser($_POST['email'], $psd) ) {
			include_once( SITE_PATH."/addons/plugins/Login/lib/{$type}.class.php" );
			$platform = new $type();
			$userinfo = $platform->userInfo();

			// 检查是否成功获取用户信息
			if ( empty($userinfo['id']) || empty($userinfo['uname']) ) {
				$this->assign('jumpUrl', SITE_URL);
				$this->error(L('user_information_filed'));
			}

			// 检查是否已加入本站
			$map['type_uid'] = $userinfo['id'];
			$map['type']     = $type;
			if ( ($local_uid = M('login')->where($map)->getField('uid')) && (M('user')->where('uid='.$local_uid)->find()) ) {
				$this->assign('jumpUrl', SITE_URL);
				$this->success(L('you_joined'));
			}

			$syncdata['uid']      = $user['uid'];
			$syncdata['type_uid'] = $userinfo['id'];
			$syncdata['type']     = $type;
			if ( M('login')->add($syncdata) ) {
				service('Passport')->registerLogin($user);

				$this->assign('jumpUrl', U('home/User/index'));
				$this->success(L('bind_success'));

			}else {
				$this->assign('jumpUrl', SITE_URL);
				$this->error(L('bind_error'));
			}
		}else {
			$this->error(L('wrong_account'));
		}
	}

	//
	public function callback(){
		include_once( SITE_PATH.'/addons/plugins/Login/lib/sina.class.php' );
		$sina = new sina();
		$sina->checkUser();
		redirect(U('home/public/otherlogin'));
	}

	public function doubanCallback() {
		if ( !isset($_GET['oauth_token']) ) {
			$this->error('Error: No oauth_token detected.');
			exit;
		}
		require_once SITE_PATH . '/addons/plugins/Login/lib/douban.class.php';
		$douban = new douban();
		if ( $douban->checkUser($_GET['oauth_token']) ) {
			redirect(U('home/Public/otherlogin'));
		}else {
			$this->assign('jumpUrl', SITE_URL);
			$this->error(L('checking_failed'));
		}
	}

	public function doLogin() {
		// 检查验证码
		$opt_verify = $this->_isVerifyOn('login');
		if ($opt_verify && (md5(strtoupper($_POST['verify']))!=$_SESSION['verify'])) {
			$this->error(L('error_security_code'));
		}

		Addons::hook('public_before_dologin',$_POST);

		$username =	$_POST['email'];
		$password =	$_POST['password'];

		if(!$username){
			$this->error('请输入帐号');
		}
		if(!$password){
			$this->error(L('please_input_password'));
		}
		$result = service('Passport')->loginLocal($username,$password,intval($_POST['remember']));
		$lastError = service('Passport')->getLastError(); 
	    //检查是否激活
	    if (!$result && $lastError =='用户未激活') {
	        $this->assign('jumpUrl',U('home/public/login'));
	        $this->error('该用户尚未激活，请更换帐号或激活帐号！');
	        exit;
	    }

		Addons::hook('public_after_dologin',$result);

		if($result) {
			if(UC_SYNC && $result['reg_from_ucenter']){
				//从UCenter导入ThinkSNS，跳转至帐号修改页
				$refer_url = U('home/Public/userinfo');
			}elseif ( $_SESSION['refer_url'] != '' ) {
				//跳转至登录前输入的url
				$refer_url	=	$_SESSION['refer_url'];
				unset($_SESSION['refer_url']);
			}else {
				$refer_url = U('home/User/index');
			}
			$this->assign('jumpUrl',$refer_url);
			$this->assign('waitSecond',10);
			$this->success($username.L('login_success').$result['login']);
		}else {
			$this->error($lastError);
		}
	}

	public function doAjaxLogin(){

		// 检查验证码
		$opt_verify = $this->_isVerifyOn('login');
		if ($opt_verify && (md5(strtoupper($_POST['verify']))!=$_SESSION['verify'])) {
			$return['message']	=	L('error_security_code');
			$return['status']	=	0;
			exit(json_encode($return));
		}

		$username =	$_POST['email'];
		$password =	$_POST['password'];

		Addons::hook('public_before_doajaxlogin',$_POST);

		if(!$password){
			$return['message']	=	L('password_notnull');
			$return['status']	=	0;
			exit(json_encode($return));
		}

		$result = service('Passport')->loginLocal($username,$password, intval($_POST['remember']) === 1);
		if($result){
			$return['message']	=	L('login_success');
			$return['status']	=	1;
			if(UC_SYNC && $uc_user[0])
				$return['callback']	=	uc_user_synlogin($uc_user[0]);
		}else{
			$error_message = service('Passport')->getLastError();
			$return['message']	=	$error_message;
			$return['status']	=	0;
		}
		
		Addons::hook('public_after_doajaxlogin',$result);

		exit(json_encode($return));
	}

	public function logout() {
		service('Passport')->logoutLocal();
		
		Addons::hook('public_after_logout');

		$this->assign('jumpUrl',U('home/Index/index'));
		$this->assign('waitSecond',5);
		$this->success(L('exit_success'). ( (UC_SYNC)?uc_user_synlogout():'' ) );
	}

	public function logoutAdmin() {
		// 成功消息不显示头部
		$this->assign('isAdmin','1');
		service('Passport')->logoutLocal();
		$this->assign('jumpUrl',U('home/Public/adminlogin'));
		$this->success(L('exit_success'));
	}

	private function __getInviteInfo($invite_code) {
		$res = null;
		$invite_option = model('Invite')->getSet();
		switch (strtolower($invite_option['invite_set'])) {
			case 'close':
				$res = null;
				break;
			case 'common':
				$res = D('User', 'home')->getUserByIdentifier($invite_code, 'uid');
				break;
			case 'invitecode':
				$res = model('Invite')->checkInviteCode($invite_code);
				if ($res['is_used'])
					$res = null;
				break;
		}

		return $res;
	}

	public function isRegisterOpen() {
	    return strtolower(model('Xdata')->get('register:register_type')) == 'open';
	}

	public function isRegisterAvailable() {
		echo $this->isRegisterOpen() ? '1' : '0';
	}
    
	public function register() {

		if (service('Passport')->isLogged())
			redirect(U('home/User/index'));
		
		//验证码
		$opt_verify = $this->_isVerifyOn('register');
		if ( $opt_verify ) {
			$this->assign('register_verify_on', 1);
		}

		Addons::hook('public_before_register');

		// 邀请码
		$invite_code = h($_REQUEST['invite']);
		$invite_info = null;

		// 是否开放注册
		$register_option = model('Xdata')->get('register:register_type');
		if ($register_option == 'closed') { // 关闭注册
			$this->error(L('reg_close'));

		} else if ($register_option == 'invite') { // 邀请注册
			// 邀请方式
			$invite_option = model('Invite')->getSet();
			if ($invite_option['invite_set'] == 'close') { // 关闭邀请
				$this->error(L('reg_invite_close'));
			} else { // 普通邀请 OR 使用邀请码
				if (!$invite_code)
					$this->error(L('reg_invite_warming'));
				else if (!($invite_info = $this->__getInviteInfo($invite_code)))
					$this->error(L('reg_invite_code_error'));
			}
		} else { // 公开注册
			if (!($invite_info = $this->__getInviteInfo($invite_code)))
				unset($invite_code, $invite_info);
		}

		$this->assign('invite_info', $invite_info);
		$this->assign('invite_code', $invite_code);
		$this->setTitle(L('reg'));
		$this->display();
	}

	public function doRegister() {
		
		$uid = intval($_POST['invite_uid']);
		// 验证码
		$verify_option = $this->_isVerifyOn('register');
		if ($verify_option && (md5(strtoupper($_POST['verify'])) != $_SESSION['verify'])){
			$this->error(L('error_security_code'));
			exit;
		}

		Addons::hook('public_before_doregister', $_POST);

		// 邀请码
		$invite_code = h($_REQUEST['invite_code']);
		$invite_info = null;

		// 是否允许注册
		$register_option = model('Xdata')->get('register:register_type');
		if ($register_option === 'closed') { // 关闭注册
			$this->error(L('reg_close'));

		} else if ($register_option === 'invite') { //邀请注册
			// 邀请方式
			$invite_option = model('Invite')->getSet();
			
			if ($invite_option['invite_set'] == 'close') { // 关闭邀请
				$this->error(L('reg_invite_close'));
			} else { // 普通邀请 OR 使用邀请码
				if (!$invite_code)
					$this->error(L('reg_invite_warming'));
				else if (!($invite_info = $this->__getInviteInfo($invite_code))){
					$this->error(L('reg_invite_code_error'));
				}

			}
		} else { // 公开注册

			if (!($invite_info = $this->__getInviteInfo($invite_code)))
				unset($invite_code, $invite_info);
		} 

		// 参数合法性检查
		$required_field = array(
			'email'		=> 'Email',
			'nickname'  => L('username'),
			'password'	=> L('password'),
			'repassword'=> L('retype_password'),
		);
		foreach ($required_field as $k => $v)
			if (empty($_POST[$k]))
				$this->error($v . L('not_null'));

		if (!$this->isValidEmail($_POST['email']))
			$this->error(L('email_format_error_retype'));
		if (!$this->isValidNickName($_POST['nickname']))
			$this->error(L('username_format_error'));
		if (strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16 || $_POST['password'] != $_POST['repassword'])
			$this->error(L('password_rule'));
		if (!$this->isEmailAvailable($_POST['email']))
			$this->error(L('email_used_retype'));

		// 是否需要Email激活
		$need_email_activate = intval(model('Xdata')->get('register:register_email_activate'));

		// 注册
		$data['email']     = $_POST['email'];
		$data['password']  = md5($_POST['password']);
		$data['uname']	   = t($_POST['nickname']);
		$data['ctime']	   = time();
		$data['is_active'] = $need_email_activate ? 0 : 1;
		$data['register_ip']= get_client_ip();
		$data['login_ip']	 = get_client_ip();
		if (!($uid = D('User', 'home')->add($data)))
			$this->error(L('reg_filed_retry'));


		Addons::hook('public_after_doregister',$uid);
		if($_POST['invite_code']){
			unset($data);
			$data['uid'] = intval($_POST['invite_uid']);
			$data['fid'] = $uid;
			$data['type'] = 0;
			M('weibo_follow')->add($data);
			$data1['uid'] = $uid;
			$data1['fid'] = intval($_POST['invite_uid']);
			$data1['type'] = 0;
			M('weibo_follow')->add($data1);
		}
		// 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
		$user_log = array(
			'uid'		=> $uid,
			'action'	=> 'add',
			'type'		=> '0',
			'dateline'	=> time(),
		);
		M('myop_userlog')->add($user_log);
        
        //添加默认分组
        $this->addFollowGroup($uid);
        
		// 将邀请码设置已用
		model('Invite')->setInviteCodeUsed($invite_code);
		model('InviteRecord')->addRecord($invite_info['uid'],$uid);

		// 同步至UCenter
		if (UC_SYNC) {

			$uc_uid = uc_user_register($_POST['nickname'],$_POST['password'],$_POST['email']);
			//echo uc_user_synlogin($uc_uid);
			if ($uc_uid > 0)
				$data['uname']	   = t($_POST['nickname']);
				ts_add_ucenter_user_ref($uid,$uc_uid,$data['uname']);
		}

		if ($need_email_activate == 1) { // 邮件激活

			$this->activate($uid, $_POST['email'], $invite_code);
		} else {
			// 置为已登录, 供完善个人资料时使用
			service('Passport')->loginLocal($uid);

			//if (!is_numeric(stripos($_POST['HTTP_REFERER'], dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']))) && $register_option != 'invite') {
                //注册完毕，跳回注册页之前
            //    redirect($_POST['HTTP_REFERER']);
			//} else {
				//注册完毕，跳转至帐号修改页
				redirect(U('home/Public/userinfo',array('uid'=>$uid)));
			//}
		}

	}

	// 完善个人资料
	public function userinfo() {

		if (!$this->mid)
			redirect(U('home/Public/login'));

		// 已初始化的用户, 不允许在此修改资料
		global $ts;
		if ($this->mid && $ts['user']['is_init'])
			redirect(U('home/User/index'));

		if ($_POST) {

			$user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
			if (!$user_info['uname']) {
				if (!$this->isValidNickName($_POST['nickname']))
					$this->error(L('nickname_format_error_or_used'));
				else
					$data['uname'] = $_POST['nickname'];
			}

			$data['sex']   	  = intval($_POST['sex']);
			$data['province'] = intval($_POST['area_province']);
			$data['city']     = intval($_POST['area_city']);
			$data['location'] = getLocation($data['province'], $data['city']);
			$data['is_init']  = 1;
			M('user')->where("uid={$this->mid}")->data($data)->save();

			// 关联操作
			$this->registerRelation($this->mid);
			$uid = intval($_GET['uid']);
			if($uid){
				D('Follow','weibo')->dofollow( $uid,$this->mid );
				D('Follow','weibo')->dofollow( $this->mid,$uid );
			}

			S('S_userInfo_'.$this->mid,null);

			redirect(U('home/Public/followuser'));
		} else {
			$user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
			$this->assign('nickname', $user_info['uname']);
			$this->setTitle(L('complete_information'));
			$this->display();
		}
	}

	//关注推荐用户
	public function followuser() {
		if ($_POST) {
			if ($_POST['followuid']) {
				foreach ($_POST['followuid'] as $value) {
					D('Follow','weibo')->dofollow($this->mid,$value,0);
				}
			}
			if ($_POST['doajax']) {
				echo '1';
			} else {
				redirect(U('home/User/index'));
			}
		} else {
			$users      = D('Follow', 'weibo')->getTopFollowerUser($this->mid, 50);
			//随机打散取10个
			shuffle($users);
			$users = array_slice($users,0,10);
			//获取用户详细信息
			$user_model = D('User', 'home');
			$user_model->setUserObjectCache(getSubByKey($users, 'uid'));
			foreach ($users as $k => $v) {
				$user = $user_model->getUserByIdentifier($v['uid'], 'uid');
				$users[$k]['uname']    = $user['uname'];
				$users[$k]['location'] = $user['location'];
			}
			$this->assign('users', $users);
			$this->setTitle(L('recommend_user'));
			$this->display();
		}
	}

	//使用邀请码注册
	public function inviteRegister() {
		if ( ! $invite = service('Validation')->getValidation() ) {
			$this->error(L('reg_invite_code_error'));
		}

		if ( "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] != $invite['target_url'] ) {
			$this->error(L('url_error'));
		}
		$this->assign('invite', $invite);

		$invite['data']			= unserialize($invite['data']);
		$map['tpl_record_id']	= $invite['data']['tpl_record_id'];
		$tpl_record 			= model('Template')->getTemplateRecordByMap($map, '', 1);
		$tpl_record 			= $tpl_record['data'][0]['data'];
		$this->assign('template', $tpl_record);

		//邀请人的好友
		$friend = model('Friend')->getFriendList($invite['from_uid'], null, 9);
		$this->assign($friend);

		$this->display('invite');
	}

	public function resendEmail() {
		$invite = service('Validation')->getValidation();
		$this->activate(intval($_GET['uid']), $_GET['email'], $invite, 1);
	}

	//发送激活邮件
	public function activate($uid, $email, $invite = '', $is_resend = 0) {
		//设置激活路径
		$activate_url  = service('Validation')->addValidation($uid, '', U('home/Public/doActivate'), 'register_activate', serialize($invite));
		if ($invite) {
			$this->assign('invite', $invite);
		}
		$this->assign('url',$activate_url);

		//设置邮件模板
		$body = <<<EOD
感谢您的注册!<br>

请马上点击以下注册确认链接，激活您的帐号！<br>

<a href="$activate_url" target='_blank'>$activate_url</a><br/>

如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;
		// 发送邮件
		global $ts;
		$email_sent = service('Mail')->send_email($email, "激活{$ts['site']['site_name']}帐号",$body);

		// 渲染输出
		if ($email_sent) {
			$email_info = explode("@", $email);
			switch ($email_info[1]) {
				case "qq.com"    : $email_url = "mail.qq.com";break;
				case "163.com"   : $email_url = "mail.163.com";break;
				case "126.com"   : $email_url = "mail.126.com";break;
				case "gmail.com" : $email_url = "mail.google.com";break;
				default          : $email_url = "mail.".$email_info[1];
			}

			$this->assign("uid",$uid);
			$this->assign('email', $email);
			$this->assign('is_resend', $is_resend);
			$this->assign("email_url",$email_url);
			$this->display('activate');
		}else {
			$this->assign('jumpUrl', U('home/Index/index'));
			$this->error(L('email_send_error_retry'));
		}
	}

	public function doActivate() {
		$invite = service('Validation')->getValidation();
		if (!$invite) {
			$this->assign('jumpUrl', U('home/Public/register'));
        	$this->error(L('activation_code_error_retry'));
		}
		$uid = $invite['from_uid'];

		$invite['data'] = unserialize($invite['data']);
		//邀请信息
		if($invite['data']){

		}
        $user = M('user')->where("`uid`=$uid")->find();
        if ($user['is_active'] == 1) {
        	$this->assign('jumpUrl', U('home/Public/login'));
        	$this->success(L('account_activity'));
        	exit;
        } else if ($user['is_active'] == 0) {
        	//激活帐户
        	$res = M('user')->where("`uid`=$uid")->setField('is_active', 1);
        	if (!$res) $this->error(L('activation_failed'));

			service('Passport')->registerLogin($user);

			//关联操作
			$this->registerRelation($user['uid']);

			service('Validation')->unsetValidation();

			$this->assign('jumpUrl', U('home/Account/index'));
			$this->success(L("activation_success"));
        } else {
        	$this->assign('jumpUrl', U('home/Public/register'));
        	$this->error(L('activation_code_error_retry'));
        }
	}
    
    //组织用户验证
    //2012.10.15
    //jianjungki
    public function doOrgActivate() {
        $invite = service('Validation')->getValidation();
        if (!$invite) {
            $this->assign('jumpUrl', U('home/Public/register'));
            $this->error(L('activation_code_error_retry'));
        }
        $uid = $invite['from_uid'];
        service('Passport')->loginLocal($uid);
        $invite['data'] = unserialize($invite['data']);
        //邀请信息
        if($invite['data']){

        }
        $user = M('user')->where("`uid`=$uid")->find();
        if ($user['is_active'] == 1) {
            $this->assign('jumpUrl', U('home/Public/orgfollowuser'));
            $this->success(L('account_activity'));
            exit;
        } else if ($user['is_active'] == 0) {
            //激活帐户
            $res = M('user')->where("`uid`=$uid")->setField('is_active', 1);
            if (!$res) $this->error(L('activation_failed'));

            service('Passport')->registerLogin($user);
            
            //关联操作
            $this->registerRelation($user['uid']);
            
            service('Validation')->unsetValidation();
            $this->assign('jumpUrl', U('home/Account/index'));
            $this->success(L("activation_success"));
        } else {
            $this->assign('jumpUrl', U('home/Public/register'));
            $this->error(L('activation_code_error_retry'));
        }
    }
    
	public function sendPassword() {
		$this->display();
	}

	public function doSendPassword() {
		$_POST["email"]	= t($_POST["email"]);
		if ( !$this->isValidEmail($_POST['email']) )
			$this->error(L('email_format_error'));

		$user =	M("user")->where('`email`="' . $_POST['email'] . '"')->find();

        if(!$user) {
        	$this->error(L("email_not_reg"));
        }else {
            $code = base64_encode( $user["uid"] . "." . md5($user["uid"] . '+' . $user["password"]) );
            $url  = U('home/Public/resetPassword', array('code'=>$code));
            $body = <<<EOD
<strong>{$user["uname"]}，你好: </strong><br/>

您只需通过点击下面的链接重置您的密码: <br/>

<a href="$url">$url</a><br/>

如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;

			global $ts;
			$email_sent = service('Mail')->send_email($user['email'], L('reset')."{$ts['site']['site_name']}".L('password'), $body);

            if ($email_sent) {
	            $this->assign('jumpUrl', SITE_URL);
	            $this->success(L('send_you_mailbox').$email.L('notice_accept'));
            }else {
            	$this->error(L('email_send_error_retry'));
            }
		}
	}

	public function resetPassword() {
		$code = explode('.', base64_decode($_GET['code']));
        $user = M('user')->where('`uid`=' . $code[0])->find();

        if ( $code[1] == md5($code[0].'+'.$user["password"]) ) {
	        $this->assign('email',$user["email"]);
	        $this->assign('code', $_GET['code']);
	        $this->display();
        }else {
        	$this->error(L("link_error"));
        }
	}

	public function doResetPassword() {
		if($_POST["password"] != $_POST["repassword"]) {
        	$this->error(L("password_same_rule"));
        }

		$code = explode('.', base64_decode($_POST['code']));
        $user = M('user')->where('`uid`=' . $code[0])->find();

        if ( $code[1] == md5($code[0] . '+' . $user["password"]) ) {
	        $user['password'] = md5($_POST['password']);
	        $res = M('user')->save($user);
			//同步设置UC密码
			include_once(SITE_PATH.'/api/uc_client/uc_sync.php');
			if(UC_SYNC){
				$ucenter_user_ref = ts_get_ucenter_user_ref($code[0]);
				$uc_res = uc_user_edit($ucenter_user_ref['uc_username'],'',$_POST['password'],'',1);
				if($uc_res == -8){
					$this->error(L('userprotected_no_right'));
				}
			}
			//去掉用户缓存信息
			S('S_userInfo_'. $code[0],null);
	        if ($res) {
	        	$this->assign('jumpUrl', U('home/Public/login'));
	        	$this->success(L('save_success'));
	        }else {
	        	$this->error(L('save_error_retry'));
	        }
        }else {
        	$this->error(L("safety_code_error"));
        }
	}

	public function doModifyEmail() {
    	if ( !$validation = service('Validation')->getValidation() ) {
    		$this->error(L('error_security_code'));
    	}
    	if ( "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] != $validation['target_url'] ) {
    		$this->error(L('url_error'));
		}

    	$validation['data'] = unserialize($validation['data']);
    	$map['uid']			= $validation['from_uid'];
    	$map['email']		= $validation['data']['oldemail'];
		if ( M('user')->where($map)->setField('email', $validation['data']['email']) ) {
			service('Validation')->unsetValidation();
			service('Passport')->logoutLocal();
			$this->assign('jumpUrl', SITE_URL);
			$this->success(L('activate_new_email_success'));
		}else {
			$this->error(L('activate_new_email_failed'));
		}
    }

	//检查Email地址是否合法
	public function isValidEmail($email) {
		if(UC_SYNC){
			$res = uc_user_checkemail($email);
			if($res == -4){
				return false;
			}else{
				return true;
			}
		}else{
			return preg_match("/[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/i", $email) !== 0;
		}
	}

	//检查Email是否可用
	public function isEmailAvailable($email = null) {
		$return_type = empty($email) ? 'ajax' 		   : 'return';
		$email		 = empty($email) ? $_POST['email'] : $email;

		$res = M('user')->where('`email`="'.$email.'"')->find();
		if(UC_SYNC){
			$uc_res = uc_user_checkemail($email);
			if($uc_res == -5 || $uc_res == -6){
				$res = true;
			}
		}

		if ( !$res ) {
			if ($return_type === 'ajax') echo 'success';
			else return true;
		}else {
			if ($return_type === 'ajax') echo L('email_used');
			else return false;
		}
	}

	// 检查验证码是否可用
	public function isVerifyAvailable($verify = null) {
		$return_type = empty($verify) ? 'ajax' : 'return';
		$verify = empty($verify) ? $_POST['verify'] : $verify;
		$verify_option = $this->_isVerifyOn('register');
		if($verify_option && md5(strtoupper($verify)) == $_SESSION['verify']) {
			if($return_type === 'ajax') {
				echo 'success';
			} else {
				return true;
			}
		} else {
			if($return_type === 'ajax') {
				echo '验证码输入错误';
			} else {
				return false;
			}
		}

	}

	// 登陆检查验证码是否可用
	public function isVerifyAvailableLogin($verify = null) {
		$return_type = empty($verify) ? 'ajax' : 'return';
		$verify = empty($verify) ? $_POST['verify'] : $verify;
		//验证码不可用
		if(empty($_POST['verify']) && empty($verify)){
			echo 'no';
		}else{
			//验证码可用
			if( md5(strtoupper($verify)) == $_SESSION['verify']) {
				// if($return_type === 'ajax') {
				// 	echo 'success';
				// } else {
				// 	echo 'false';
				// }
				echo 'success';
			} else{
				// if($return_type === 'return') {
				// 	echo 'false';
				// } else {
				// 	echo 'success';
				// }
				echo 'false';
			}
		}
	}

	//检查昵称是否符合规则，且是否为唯一
	public function isValidNickName($name) {

		$return_type  = empty($name)  ? 'ajax' 		   			: 'return';
		$name		  = empty($name)  ? t($_POST['nickname']) 	: $name;
		
		//昵称不能是纯数字昵称
		if(is_numeric($name)){
			echo '昵称不能是纯数字昵称';
			return;
		}

		//检查禁止注册的用户昵称
		$audit = model('Xdata')->lget('audit');
		if($audit['banuid']==1){
			$bannedunames = $audit['bannedunames'];
			if(!empty($bannedunames)){
				$bannedunames = explode('|',$bannedunames);
				if(in_array($name,$bannedunames)){
					if ($return_type === 'ajax') {
						echo '这个昵称禁止注册';
						return;
					} else {
						$this->error('这个昵称禁止注册');
					}
				}
			}
		}

		if (UC_SYNC) {
			$uc_res = uc_user_checkname($name);
			if($uc_res == -1 || !isLegalUsername($name)){
				if ($return_type === 'ajax') { echo L('username_rule');return; }
				else return false;
			}
		} else if (!isLegalUsername($name)) {
			if ($return_type === 'ajax') { echo L('username_rule');return; }
			else return false;
		} else if (checkKeyWord($name)) {
			if ($return_type === 'ajax') {
				echo '昵称含有敏感词';
				return;
			} else {
				$this->error('昵称含有敏感词');
			}
		}

		if ($this->mid) {
			$res = M('user')->where("uname='{$name}' AND uid<>{$this->mid}")->count();
			$nickname = M('user')->getField('uname',"uid={$this->mid}");
			if (UC_SYNC && ($uc_res == -2 || $uc_res == -3) && $nickname != $name) {
				$res = 1;
			}
		} else {
			$res = M('user')->where("uname='{$name}'")->count();
			if(UC_SYNC && ($uc_res == -2 || $uc_res == -3)){
				$res = 1;
			}
		}

		if ( !$res ) {
			if ($return_type === 'ajax') echo 'success';
			else return true;
		}else {
			if ($return_type === 'ajax') echo L('username_used');
			else return false;
		}
	}
	//检查简称是否符合规则，且是否为唯一
	public function isValidUnitFullName($name){
		

		$return_type  = empty($name)  ? 'ajax' 		   			: 'return';
		$name		  = empty($name)  ? t($_POST['unitFullName']) 	: $name;
		
		//简称不能是纯数字昵称
		if(is_numeric($name)){
			echo '简称不能是纯数字简称';
			return;
		}
		
		//检查禁止注册的用户简称
		$audit = model('Xdata')->lget('audit');
		if($audit['banuid']==1){
			$bannedunames = $audit['bannedunames'];
			if(!empty($bannedunames)){
				$bannedunames = explode('|',$bannedunames);
				if(in_array($name,$bannedunames)){
					if ($return_type === 'ajax') {
						echo '这个简称禁止注册';
						return;
					} else {
						$this->error('这个简称禁止注册');
					}
				}
			}
		}
		
		if (UC_SYNC) {
			$uc_res = uc_user_checkname($name);
			if($uc_res == -1 || !isLegalUsername($name,2,50)){
				if ($return_type === 'ajax') { echo '2-50位的中英文、数字、下划线、中划线';return; }
				else return false;
			}
		} else if (!isLegalUsername($name,2,50)) {
			if ($return_type === 'ajax') { echo '2-50位的中英文、数字、下划线、中划线';return; }
			else return false;
		} else if (checkKeyWord($name)) {
			if ($return_type === 'ajax') {
				echo '简称含有敏感词';
				return;
			} else {
				$this->error('简称含有敏感词');
			}
		}
		
		if ($this->mid) {
			$res = M('user')->where("uname='{$name}' AND uid<>{$this->mid}")->count();
			$nickname = M('user')->getField('uname',"uid={$this->mid}");
			if (UC_SYNC && ($uc_res == -2 || $uc_res == -3) && $unitShortName != $name) {
				$res = 1;
			}
		} else {
			$res = M('user')->where("uname='{$name}'")->count();
			if(UC_SYNC && ($uc_res == -2 || $uc_res == -3)){
				$res = 1;
			}
		}
		
		if ( !$res ) {
			if ($return_type === 'ajax') echo 'success';
			else return true;
		}else {
			if ($return_type === 'ajax') echo L('username_used');
			else return false;
		}
		
	}

	//检查行业类型是否符合规则
	public function isValidCategory(){
		
		$return_type  = empty($name)  ? 'ajax' 		   			: 'return';
		$name		  = empty($name)  ? t($_POST['category']) 	: $name;
		
		//简称不能是纯数字昵称
		if(is_numeric($name)){
			echo '简称不能是纯数字简称';
			return;
		}
		
		//检查禁止注册的用户简称
		$audit = model('Xdata')->lget('audit');
		if($audit['banuid']==1){
			$bannedunames = $audit['bannedunames'];
			if(!empty($bannedunames)){
				$bannedunames = explode('|',$bannedunames);
				if(in_array($name,$bannedunames)){
					if ($return_type === 'ajax') {
						echo '这个简称禁止注册';
						return;
					} else {
						$this->error('这个简称禁止注册');
					}
				}
			}
		}
		
		if (UC_SYNC) {
			$uc_res = uc_user_checkname($name);
			if($uc_res == -1 || !isLegalTypename($name)){
				if ($return_type === 'ajax') { echo L('username_rule');return; }
				else return false;
			}
		} else if (!isLegalTypename($name)) {
			if ($return_type === 'ajax') { echo L('username_rule');return; }
			else return false;
		} else if (checkKeyWord($name)) {
			if ($return_type === 'ajax') {
				echo '类型含有敏感词';
				return;
			} else {
				$this->error('类型含有敏感词');
			}
		}
		
		if ($this->mid) {
			$res = M('user')->where("uname='{$name}' AND uid<>{$this->mid}")->count();
			$nickname = M('user')->getField('uname',"uid={$this->mid}");
			if (UC_SYNC && ($uc_res == -2 || $uc_res == -3) && $nickname != $name) {
				$res = 1;
			}
		} else {
			$res = M('user')->where("uname='{$name}'")->count();
			if(UC_SYNC && ($uc_res == -2 || $uc_res == -3)){
				$res = 1;
			}
		}
		
		if ( !$res ) {
			if ($return_type === 'ajax') echo 'success';
			else return true;
		}else {
			if ($return_type === 'ajax') echo L('username_used');
			else return false;
		}
		
		
	}
	
	public function isValidUserName($name){
		
		$return_type  = empty($name)  ? 'ajax' 		   			: 'return';
		$name		  = empty($name)  ? t($_POST['username']) 	: $name;
		
		//姓名不能是纯数字昵称
		if(is_numeric($name)){
			echo '姓名不能是纯数字简称';
			return;
		}
		
		//检查禁止注册的用户简称
		$audit = model('Xdata')->lget('audit');
		if($audit['banuid']==1){
			$bannedunames = $audit['bannedunames'];
			if(!empty($bannedunames)){
				$bannedunames = explode('|',$bannedunames);
				if(in_array($name,$bannedunames)){
					if ($return_type === 'ajax') {
						echo '这个姓名禁止注册';
						return;
					} else {
						$this->error('这个姓名禁止注册');
					}
				}
			}
		}
		
		/*if (UC_SYNC) {
			$uc_res = uc_user_checkname($name);
			if($uc_res == -1 || !isLegalTypename($name)){
				if ($return_type === 'ajax') { echo L('username_rule');return; }
				else return false;
			}
		} else if (!isLegalTypename($name)) {
			if ($return_type === 'ajax') { echo L('username_rule');return; }
			else return false;
		} else */
		if (checkKeyWord($name)) {
			if ($return_type === 'ajax') {
				echo '姓名含有敏感词';
				return;
			} else {
				$this->error('姓名含有敏感词');
			}
		}
		
		if ($this->mid) {
			$res = M('user')->where("uname='{$name}' AND uid<>{$this->mid}")->count();
			$nickname = M('user')->getField('uname',"uid={$this->mid}");
			if (UC_SYNC && ($uc_res == -2 || $uc_res == -3) && $nickname != $name) {
				$res = 1;
			}
		} else {
			$res = M('user')->where("uname='{$name}'")->count();
			if(UC_SYNC && ($uc_res == -2 || $uc_res == -3)){
				$res = 1;
			}
		}
		
		if ( !$res ) {
			if ($return_type === 'ajax') echo 'success';
			else return true;
		}else {
			if ($return_type === 'ajax') echo L('username_used');
			else return false;
		}
		
	}
	
	
	public function isValidTelephone($name){
		
		$return_type  = empty($name)  ? 'ajax' 		   			: 'return';
		$name		  = empty($name)  ? t($_POST['telephone']) 	: $name;
		
		//电话不能是纯数字昵称
		if(!is_numeric($name)){
			echo '电话应该是纯数字';
			return;
		}else{
			if ($return_type === 'ajax') echo 'success';
			else return true;
			
		} 

		
	}
	
	//检查是否真实姓名。支持ajax和return
	public function isValidRealName($name = null, $opt_register = null) {
		$return_type  = empty($name) 		 ? 'ajax' 							: 'return';
		$name		  = empty($name) 		 ? t($_POST['uname']) 				: $name;
		$opt_register = empty($opt_register) ? model('Xdata')->lget('register') : $opt_register;

		if ($opt_register['register_realname_check'] == 1) {
			$lastname = explode(',', $opt_register['register_lastname']);
			$res = in_array( substr($name, 0, 3), $lastname ) || in_array( substr($name, 0, 6), $lastname );
		}else {
			$res = true;
		}

		if ($res) {
			if ($return_type === 'ajax') echo 'success';
			else return true;
		}else {
			if ($return_type === 'ajax') echo 'fail';
			else return false;
		}
	}

	// 注册的关联操作
    public function registerRelation($uid,$invite_uid) {
    	if (($uid = intval($uid)) <= 0)
    		return;

    	$dao = D('Follow','weibo');
   
    	// 使用邀请码时, 建立与邀请人的关系
    	$inviter = model('InviteRecord')->getInviter($uid);
    	if ($inviter) {
    		// 互相关注
    		$dao->dofollow($uid, $inviter['uid']);
			$dao->dofollow($inviter['uid'], $uid);

			// 设置邀请信息
			model('InviteRecord')->setRecordValid($uid);

			// 添加邀请记录
			//model('InviteRecord')->addRecord($inviter['uid'], $uid);

			//邀请人积分操作
			X('Credit')->setUserCredit($inviter['uid'], 'invite_friend');
    	}

        // 默认关注的好友
		$auto_freind = model('Xdata')->lget('register');
		$auto_freind['register_auto_friend'] = explode(',', $auto_freind['register_auto_friend']);
		foreach($auto_freind['register_auto_friend'] as $v) {
			if (($v = intval($v)) <= 0)
				continue ;
			$dao->dofollow($v, $uid);
			$dao->dofollow($uid, $v);
		}

		// 开通个人空间
		$data['uid'] = $uid;
		model('Space')->add($data);

		//注册成功 初始积分
		X('Credit')->setUserCredit($uid,'init_default');
	}

	public function verify() {
        require_once(SITE_PATH.'/addons/libs/Image.class.php');
        require_once(SITE_PATH.'/addons/libs/String.class.php');
    	Image::buildImageVerify();
	}

    //上传图片
    public function uploadpic(){
    	if( $_FILES['pic'] ){
    		//执行上传操作
    		$savePath =  $this->getSaveTempPath();
    		$filename = md5( time().'teste' ).'.'.substr($_FILES['pic']['name'],strpos($_FILES['pic']['name'],'.')+1);
	    	if(@copy($_FILES['pic']['tmp_name'], $savePath.'/'.$filename) || @move_uploaded_file($_FILES['pic']['tmp_name'], $savePath.'/'.$filename))
	        {
	        	$result['boolen']    = 1;
	        	$result['type_data'] = 'temp/'.$filename;
	        	$result['picurl']    = __UPLOAD__.'/temp/'.$filename;
	        } else {
	        	$result['boolen']    = 0;
	        	$result['message']   = L('upload_filed');
	        }
    	}else{
        	$result['boolen']    = 0;
        	$result['message']   = L('upload_filed');
    	}

    	exit( json_encode( $result ) );
    }

    //上传临时文件
	public function getSaveTempPath(){
        $savePath = SITE_PATH.'/data/uploads/temp';
        if( !file_exists( $savePath ) ) mk_dir( $savePath  );
        return $savePath;
    }

    // 地区管理
    public function getArea() {
    	echo json_encode(model('Area')->getAreaTree());
    }

	/**  文章  **/
	public function document() {
		$list	= array();
		$detail = array();
		$res    = M('document')->where('`is_active`=1')->order('`display_order` ASC,`document_id` ASC')->findAll();

		// 获取content为url且在页脚显示的文章
		global $ts;
		$ids_has_url = array();
		foreach($ts['footer_document'] as $v)
			if( !empty($v['url']) )
				$ids_has_url[] = $v['document_id'];

		$_GET['id'] = intval($_GET['id']);

		foreach($res as $v) {
			// 不显示content为url且在页脚显示的文章
			if ( in_array($v['document_id'], $ids_has_url) )
				continue ;

			$list[] = array('document_id'=>$v['document_id'], 'title'=>$v['title']);

			// 当指定ID，且该ID存在，且该文章的内容不是url时，显示指定的文章。否则显示第一篇
			if ( $v['document_id'] == $_GET['id'] || empty($detail) ) {
				$v['content'] = htmlspecialchars_decode($v['content']);
				$detail = $v;
			}
		}
		unset($res);

		$this->assign('detail', $detail);
		$this->assign('list', $list);
		$this->display();
	}

	public function toWap() {
		$_SESSION['wap_to_normal'] = '0';
		cookie('wap_to_normal', '0', 3600*24*365);
		U('wap', '', true);
	}

	public function fetchNew()
	{
		$map['weibo_id']	 = array('gt', intval($_POST['since_id']));
		$map['transpond_id'] = 0;
		$map['type']		 = 0;
		$data = D('Operate', 'weibo')->doSearchTopic('`weibo_id` > '.intval($_POST['since_id']).' AND transpond_id =0 AND `type` = 0', 'weibo_id DESC', 0,false);
		$res = $data['data'][0];
		if ($res) {
			$res['uname'] = getUserSpace($res['uid'], '', '_blank', '{uname}');
			$res['user_pic']	  = getUserSpace($res['uid'], '', '_blank', '{uavatar=m}');
			$res['friendly_date'] = friendlyDate($res['ctime']);
			$res['content'] = format($res['content'],true);
			echo json_encode($res);
		} else {
			echo 0;
		}
	}

	public function error404() {
		$this->display('404');
	}

	private function _isVerifyOn($type='login'){
		// 检查验证码
		if($type!='login' && $type!='register') return false;
		$opt_verify = $GLOBALS['ts']['site']['site_verify'];
		return in_array($type, $opt_verify);
	}

	public function displayAddons(){
        $result = array();
        $param['res'] = &$result;
        $param['type'] = $_REQUEST['type'];
        Addons::addonsHook($_GET['addon'],$_GET['hook'],$param);
        isset($result['url']) && $this->assign("jumpUrl",$result['url']);
        isset($result['title']) && $this->setTitle($result['title']);
        isset($result['jumpUrl']) && $this->assign('jumpUrl',$result['jumpUrl']);
        if(isset($result['status']) && !$result['status']){
            $this->error($result['info']);
        }
        if(isset($result['status']) && $result['status']){
            $this->success($result['info']);
        }
	}

	/*
	 * 组织注册功能
	 * @return void
     * @author 
	 * time: 2012-09-10
	 * 基于原有的注册功能
	 */
	 public function orgRegister()
	{
		if (service('Passport')->isLogged())
			redirect(U('home/User/index'));
		//验证码
		$opt_verify = $this->_isVerifyOn('register');
		if ( $opt_verify ) {
			$this->assign('register_verify_on', 1);
		}

		Addons::hook('public_before_register');

		// 邀请码
		$invite_code = h($_REQUEST['invite']);
		$invite_info = null;

		// 是否开放注册
		$register_option = model('Xdata')->get('register:register_type');
		if ($register_option == 'closed') { // 关闭注册
			$this->error(L('reg_close'));

		} else if ($register_option == 'invite') { // 邀请注册
			// 邀请方式
			$invite_option = model('Invite')->getSet();
			if ($invite_option['invite_set'] == 'close') { // 关闭邀请
				$this->error(L('reg_invite_close'));
			} else { // 普通邀请 OR 使用邀请码
				if (!$invite_code)
					$this->error(L('reg_invite_warming'));
				else if (!($invite_info = $this->__getInviteInfo($invite_code)))
					$this->error(L('reg_invite_code_error'));
			}
		} else { // 公开注册
			if (!($invite_info = $this->__getInviteInfo($invite_code)))
				unset($invite_code, $invite_info);
		}
		$this->assign('invite_info', $invite_info);
		$this->assign('invite_code', $invite_code);
		$this->setTitle(L('reg'));
		$this->display();
	}
	/**
	 * doorgRegister function
	 * 企业组织注册功能
	 * @return void
	 * @author jianjungki 
	 */
	public function doorgRegister()
	{
		
		$uid = intval($_POST['invite_uid']);
		// 验证码
		$verify_option = $this->_isVerifyOn('register');
		if ($verify_option && (md5(strtoupper($_POST['verify'])) != $_SESSION['verify'])){
			$this->error(L('error_security_code'));
			exit;
		}

		Addons::hook('public_before_doregister', $_POST);

		// 邀请码
		$invite_code = h($_REQUEST['invite_code']);
		$invite_info = null;

		// 是否允许注册
		$register_option = model('Xdata')->get('register:register_type');
		if ($register_option === 'closed') { // 关闭注册
			$this->error(L('reg_close'));

		} else if ($register_option === 'invite') { //邀请注册
			// 邀请方式
			$invite_option = model('Invite')->getSet();
			
			if ($invite_option['invite_set'] == 'close') { // 关闭邀请
				$this->error(L('reg_invite_close'));
			} else { // 普通邀请 OR 使用邀请码
				if (!$invite_code)
					$this->error(L('reg_invite_warming'));
				else if (!($invite_info = $this->__getInviteInfo($invite_code))){
					$this->error(L('reg_invite_code_error'));
				}

			}
		} else { // 公开注册

			if (!($invite_info = $this->__getInviteInfo($invite_code)))
				unset($invite_code, $invite_info);
		}

		// 参数合法性检查
		$required_field = array(
			'email'		=> 'Email',
			'nickname'  => L('username'),
			'password'	=> L('password'),
			'repassword'=> L('retype_password'),
		);
		foreach ($required_field as $k => $v)
			if (empty($_POST[$k]))
				$this->error($v . L('not_null'));

		if (!$this->isValidEmail($_POST['email']))
			$this->error(L('email_format_error_retype'));
		if (!$this->isValidNickName($_POST['nickname']))
			$this->error(L('username_format_error'));
		if (strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16 || $_POST['password'] != $_POST['repassword'])
			$this->error(L('password_rule'));
		if (!$this->isEmailAvailable($_POST['email']))
			$this->error(L('email_used_retype'));

		// 是否需要Email激活
		$need_email_activate = intval(model('Xdata')->get('register:register_email_activate'));
        
        $User = D('User', 'home');
		$User->startTrans();
        
        //组织用户注册的额外信息
        $data['unitFullName']     = t($_POST['unitFullName']);
        $data['unitShortName']    = t($_POST['nickname']);
        $data['category']         = t($_POST['category']);
        $data['username']         = t($_POST['username']);
        $data['telephone']        = t($_POST['telephone']);
        $data['fileId']           = $res;
        if (!($orguid = D('OrgUser', 'home')->add($data)))
            $this->error(L('reg_filed_retry'));
        
        // 注册
        $data['email']       = $_POST['email'];
        $data['password']    = md5($_POST['password']);
        $data['uname']       = t($_POST['nickname']);
        $data['ctime']       = time();
        $data['is_active']   = 1;//需要验证
        $data['register_ip'] = get_client_ip();
        $data['login_ip']    = get_client_ip();
        $data['login_ip']    = get_client_ip();
        $data['deputyoriid'] = $orguid;
        
        if (!($uid = $User->add($data))){
            $User->rollback();
            $this->error(L('reg_filed_retry'));
        }  
        else
            $User->commit();
        
        //组织与用户的关联关系
        /*$data['orgid']           = $orguid;
        $data['uid']             = $useruid;
        $data['isaddresser']     = 1;
        $data['isblack']         = 0;
        $data['request']         = 0;
        
        if (!($uid = D('OrgUserlink', 'home')->add($data)))
            $this->error(L('reg_filed_retry'));
        */
		Addons::hook('public_after_doregister',$uid);
		if($_POST['invite_code']){
			unset($data);
			$data['uid'] = intval($_POST['invite_uid']);
			$data['fid'] = $uid;
			$data['type'] = 0;
			M('weibo_follow')->add($data);
			$data1['uid'] = $uid;
			$data1['fid'] = intval($_POST['invite_uid']);
			$data1['type'] = 0;
			M('weibo_follow')->add($data1);
		}
		// 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
		$user_log = array(
			'uid'		=> $uid,
			'action'	=> 'add',
			'type'		=> '0',
			'dateline'	=> time(),
		);
		M('myop_userlog')->add($user_log);

        //添加默认分组
        $this->addFollowGroup($uid);
        
		// 将邀请码设置已用
		model('Invite')->setInviteCodeUsed($invite_code);
		model('InviteRecord')->addRecord($invite_info['uid'],$uid);

		// 同步至UCenter
		if (UC_SYNC) {
			$uc_uid = uc_user_register($_POST['nickname'],$_POST['password'],$_POST['email']);
			//echo uc_user_synlogin($uc_uid);
			if ($uc_uid > 0)
				$data['uname']	   = t($_POST['nickname']);
				ts_add_ucenter_user_ref($uid,$uc_uid,$data['uname']);
		}

		// if ($need_email_activate == 1) { // 邮件激活
			// //$this->activate($uid, $_POST['email'], $invite_code);
		// } else {
			// 置为已登录, 供完善个人资料时使用
			service('Passport')->loginLocal($uid);

			//if (!is_numeric(stripos($_POST['HTTP_REFERER'], dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']))) && $register_option != 'invite') {
                //注册完毕，跳回注册页之前
            //    redirect($_POST['HTTP_REFERER']);
			//} else {
				//注册完毕，跳转至帐号修改页
				redirect(U('home/Public/verifydata',array('uid'=>$uid)));
			//}
		// }

	}

    /*
     * 添加公共分组
     * @return void
     * @author jianjungki
     */ 
     function addFollowGroup($uid){
         $data = array(
           0 => array(
                            'uid'   => $uid,
                            'title' => "同事",
                            'ctime' => time()
            ),
           1 => array(
                            'uid'   => $uid,
                            'title' => "同学",
                            'ctime' => time()
            ),
           2 => array(
                            'uid'   => $uid,
                            'title' => "朋友",
                            'ctime' => time()
            ),
           3 => array(
                            'uid'   => $uid,
                            'title' => "圈友",
                            'ctime' => time()
            )
         );
        foreach ($data as $value) {
            M('WeiboFollowGroup')->add($value);
            
        }
     }
     
    
        
    
    /**
     * verifydata function
     * 企业内容填写
     * @return void
     * @author  jianjungki
     */
    public function verifydata(){
        if (!$this->mid)
            redirect(U('home/Public/login'));

        // 已初始化的用户, 不允许在此修改资料
        global $ts;
        if ($this->mid && $ts['user']['is_init'])
            redirect(U('home/User/index'));
		
		//创建组织私密圈
		$this->createCloseGroup($this->mid);
		
        if ($_POST) {

            $user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
            if (!$user_info['uname']) {
                if (!$this->isValidNickName($_POST['nickname']))
                    $this->error(L('nickname_format_error_or_used'));
                else
                    $data['uname'] = $_POST['nickname'];
            }
    
            $data['sex']      = intval($_POST['sex']);
            $data['province'] = intval($_POST['area_province']);
            $data['city']     = intval($_POST['area_city']);
            $data['location'] = getLocation($data['province'], $data['city']);
            $data['is_init']  = 1;
            M('user')->where("uid={$this->mid}")->data($data)->save();

            // 关联操作
            $this->registerRelation($this->mid);
            $uid = intval($_GET['uid']);
            if($uid){
                D('Follow','weibo')->dofollow( $uid,$this->mid );
                D('Follow','weibo')->dofollow( $this->mid,$uid );
            }

            S('S_userInfo_'.$this->mid,null);

            redirect(U('home/Public/verifyfile'));
        } else {
            $user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
            $this->assign('nickname', $user_info['uname']);
            $this->setTitle(L('org_complete_information'));
            $this->display();
        }
    }
    /**
     * verifydata function
     * 上传企业凭证函数，进行企业信息插入
     * @return void
     * @author  jianjungki
     */
    public function verifyfile(){
        if (!$this->mid)
            redirect(U('home/Public/login'));
        if ($_POST) {
            $user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
            $org_info = D('OrgUser', 'home')->where("orgid = {$user_info['deputyoriid']}")->find();
            if (!$user_info['uname']) {
                if (!$this->isValidNickName($_POST['nickname']))
                    $this->error(L('nickname_format_error_or_used'));
                else
                    $data['uname'] = $_POST['nickname'];
            }
            
            $data['attachment']=$_POST['attach']['0'];
            $data['uid']      = $this->mid;
            $data['realname'] = preg_match('/^[\x{2e80}-\x{9fff}]+$|^[a-zA-Z\.·]+$/u', $user_info['uname']) ? $user_info['uname'] : '';
            $data['phone']    = $org_info['telephone'];
            $data['reason']   = h("组织认证");
            $data['verified'] = '0';
            $data['veritype'] = '组织认证';
            $data['info'] = '组织认证';
            
            $res = M('user_verified')->add($data);
            if (false !== $res) {
                $data['is_active']  = 0;
                M('user')->where("uid={$this->mid}")->data($data)->save();
                S('S_userInfo_'.$this->mid, NULL);         
                service('Passport')->logoutLocal();
                 $this->assign('jumpUrl', U('home/Index/index'));
                $this->success(L('register_success'));
            } else {
                M('user_verified')->save($data);
                $this->success('更新成功');
                // echo 0;
            }
            
        } else {
            $user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
            $this->assign('nickname', $user_info['uname']);
            $this->setTitle(L('org_complete_information'));
            $this->display();
        }
    }
    
    //关注推荐用户
    public function orgfollowuser()
    {
        if ($_POST) {
            if ($_POST['followuid']) {
                foreach ($_POST['followuid'] as $value) {
                    D('Follow','weibo')->dofollow($this->mid,$value,0);
                }
            }
            if ($_POST['doajax']) {
                echo '1';
            } else {
                redirect(U('home/User/index'));
            }
        } else {
            $users      = D('Follow', 'weibo')->getTopFollowerUser($this->mid, 50);
            //随机打散取10个
            shuffle($users);
            $users = array_slice($users,0,10);
            //获取用户详细信息
            $user_model = D('User', 'home');
            $user_model->setUserObjectCache(getSubByKey($users, 'uid'));
            foreach ($users as $k => $v) {
                $user = $user_model->getUserByIdentifier($v['uid'], 'uid');
                $users[$k]['uname']    = $user['uname'];
                $users[$k]['location'] = $user['location'];
            }
            $this->assign('users', $users);
            $this->setTitle(L('recommend_user'));
            $this->display();
        }
    }
    /**
     * completedata function
     * 直接完成注册，跳转到主页
     * @return void
     * @author  jianjungki
     */
    public function completedata(){
        if (!$this->mid)
            redirect(U('home/Public/login'));

        // 已初始化的用户, 不允许在此修改资料
        global $ts;
        if ($this->mid && $ts['user']['is_init'])
            redirect(U('home/User/index'));

        if ($_POST) {

            $user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
            if (!$user_info['uname']) {
                if (!$this->isValidNickName($_POST['nickname']))
                    $this->error(L('nickname_format_error_or_used'));
                else
                    $data['uname'] = $_POST['nickname'];
            }

            $data['sex']      = intval($_POST['sex']);
            $data['province'] = intval($_POST['area_province']);
            $data['city']     = intval($_POST['area_city']);
            $data['location'] = getLocation($data['province'], $data['city']);
            $data['is_init']  = 1;
            M('user')->where("uid={$this->mid}")->data($data)->save();

            // 关联操作
            $this->registerRelation($this->mid);
            $uid = intval($_GET['uid']);
            if($uid){
                D('Follow','weibo')->dofollow( $uid,$this->mid );
                D('Follow','weibo')->dofollow( $this->mid,$uid );
            }

            S('S_userInfo_'.$this->mid,null);

            redirect(U('home/User/index'));
        } else {
            $user_info = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
            $this->assign('nickname', $user_info['uname']);
            $this->setTitle(L('complete_information'));
            $this->display();
        }
    }


    /**
     * registerSelect function
     * 注册选择页面
     * @return void
     * @author  jianjungki
     */
    public function registerSelect(){
        if (service('Passport')->isLogged())
            redirect(U('home/User/index'));
        $this->display("register");
    }
    
    
    /*
     * 成员申请加入操作
     * @time:2012.09.29
     * @author:jianjungki
     */
     function AttendTo(){
         $orginfo = M('OrgUser')->where("orgid = ".$_REQUEST['orgid']." AND uid = ".$this->mid)->find();
         $gid = D('Group','group')->where("deputyorgid = ".$_REQUEST['orgid'])->getField("id");
         if(!$orginfo){
             //在组织内加入一个成员
             
             $result = D('Group','group')->joinGroup($this->mid, $gid, 3, false,"申请加入");   //加入
             $data['uid'] = $this->mid;
             $data['orgid'] = $_REQUEST['orgid'];
             $data['request'] = 2;
             $data['requestdate'] = time();
             $data['isaddresser'] = 0;
             $data['level'] = 3;
             if(M('OrgUser')->data($data)->add()){
                 //发私信通知组织
                 $message_data['title']   = "申请加入组织";
                 $url = '<a href="' . U('home/OrgAccount/managemem', array('type'=>'apply')).'" target="_blank">去看看</a>';
                 $message_data['content'] = "你好，“{$this->user['uname']}”申请加入组织 ，点击   " . $url . '进入。';
                 $message_data['to'] = $_REQUEST['uid'];
                 $res = model('Message')->postMessage($message_data,  $this->mid);
                 $this->success("请等待组织审核");
             }
             else
                 $this->error('提交失败');
         }else if($orginfo['request']==0){
             $this->error("你已经加入该组织");
         }else if($orginfo['request']==1){
             $result = D('Group','group')->joinGroup($this->mid, $gid, 3, true,"申请加入");   //加入
             M('OrgUser')->where("orgid = ".$_REQUEST['orgid']." AND uid = ".$this->mid)->setField(array("request","ctime"),array(0,time()));
             $this->success("加入组织成功");
         }else if($orginfo['request']==2){
             $this->error("您已经提交过申请，请勿重复提交！");
         }
     }
     /*
      * 退出该组织
      * @time: 2012.10.07
      * @author: jianjungki
      */
      function Backout(){
          
          $orginfo = M('OrgUser')->where("orgid = ".$_REQUEST['orgid']." AND uid = ".$this->mid)->find();
          $gid = D('Group','group')->where("deputyorgid = ".$_REQUEST['orgid'])->getField("id");
         if(!$orginfo){
             $this->error("你没有加入该组织");
         }else{
             D('OrgUserlink')->where("orgid = ".$_REQUEST['orgid']." AND uid = ".$this->mid)->delete();
             D('Member','group')->where('gid = '.$gid.' and uid = '.$this->mid)->delete();
             $this->success("退出组织成功");
         }
      }
      
    /*
     * @author:jianjungki
     * @time:2012.10.12
     */
     //换一换推荐的人/组织
    public function replaceUser()
    {
            $limit = $_POST['limit'];
            $num = $_POST['count']*$limit;
            $type = $_POST['type'];

            $html  = '';
            
            if($type=="orguser")$recommend = S('_recommend_org_cache');
            else if($type=="user")$recommend = S('_recommend_user_cache');
            $alllimit = ($num+$limit)>=count($recommend)?count($recommend)-$num:$limit;
            for ($i = $num; $i < $alllimit+$num; $i++) {
                $shifted_user = $recommend[$i];
                $html .= "<li>";
                $html .=     '<a class="userface" target="_blank" href="'.U('home/Space/index',array('uid'=>$shifted_user['uid'])).'" rel="face" uid="'.$shifted_user['uid'].'">';
                $html .=         '<img alt="'.getUserName($shifted_user['uid']).'" src="'.getUserFace($shifted_user['uid']).'" />';
                $html .=     '</a>';
                $html .=     '<a class="userface" target="_blank" href="'.U('home/Space/index',array('uid'=>$shifted_user['uid'])).'" rel="face" uid="'.$shifted_user['uid'].'">';
                $html .=         '<span>'.getShort(getUserName($shifted_user['uid']),3,'...').'</span>';
                $html .=     '</a>';
                $html .= '</li>';
            }
            echo $html;
        
    }
	/*
      * 组织激活时创建私密圈
      * @time: 2012.10.18
      * @author: lianggh
      */
	
	public function createCloseGroup($uid)
	{
		// $uid = 40;	
		$map['orgid'] = $this->orgid;
		$name = M('org_info')->field('unitFullName')->where($map)->select();
		$name = getSubByKey($name,'unitFullName');
		
		$group['uid']   = $uid;
		$group['name']  = $name[0];
	
		$group['cid0']  = 2;
		$group['cid1']  = 20;
		$group['type']  = 'close';

		// $group['need_invite']  = intval($this->config[$group['type'] . '_invite']);  //是否需要邀请'brower_level'] = $_POST['type'] == 'open'?'-1':'1'; //浏览权限
		// $group['need_invite'] = ($_POST['need_invite'] == 1 || $_POST['need_invite'] == 2)?intval($_POST['need_invite']):0;
		// $group['openWeibo'] = intval($this->config['openWeibo']);
		// $group['openUploadFile'] = intval($this->config['openUploadFile']);
		$group['brower_level'] = 1; //浏览权限
		$group['whoUploadFile'] = 3;
		$group['whoDownloadFile'] = 3;
		// $group['openAlbum'] = intval($this->config['openAlbum']);
		$group['whoCreateAlbum'] = 2;
		$group['whoUploadPic'] = 2;
		$group['ctime'] = time();
		$group['logo'] = 'default.gif';
		
		//标识组织私密圈字段
		$group['deputyorgid'] = $this->orgid;
		$group['grouptype'] = 2;
		
		$gmap['uid'] = $group['uid'];
		$gmap['name'] = $group['name'];
		$flag = D('Group','group')->where($gmap)->select();
		if (!$flag) {
			$gid = D('Group','group')->add($group);
			// dump($flag);
		}

		if($gid) {
			// 把自己添加到成员里面
			D('Group','group')->joingroup($uid, $gid, 1, $incMemberCount=true);
			// dump(D('Group','group'));
			S('Cache_MyGroup_'.$uid,null);
		} 
		
	}


    public function RecomOrgUser(){
            $limit = $_POST['limit'];
            $num = $_POST['count']*$limit;
            $type = $_POST['type'];
            $html  = '';
            
            if($type=="orguser")$recommend = S('_recommend_org_cache');
            else if($type=="user")$recommend = S('_recommend_user_cache');
            $alllimit = ($num+$limit)>=count($recommend)?count($recommend)-$num:$limit;
            for ($i = $num; $i < $alllimit+$num; $i++) {
                $shifted_user = $recommend[$i];
                $html .= "<li id='orgPic'>";
                $html .=     '<a class="userface" target="_blank" href="'.U('home/Space/index',array('uid'=>$shifted_user['uid'])).'" rel="face" uid="'.$shifted_user['uid'].'">';
                $html .=         '<img alt="'.getUserName($shifted_user['uid']).'" src="'.getUserFace($shifted_user['uid']).'" />';
                $html .=     '</a>';
                $html .=     '<a class="userface" target="_blank" href="'.U('home/Space/index',array('uid'=>$shifted_user['uid'])).'" rel="face" uid="'.$shifted_user['uid'].'">';
                $html .=         '<span>'.getShort(getUserName($shifted_user['uid']),3,'...').'</span>';
                $html .=     '</a>';
                $html .= '</li>';
            }
            echo $html;
    }
	/*
	 * 推荐操作
	 * 2012-11-06
	 * lianggh
	 * */
	 
    public function doChangeRecommend(){
        	
			
			dump($_POST['uid']);
            /*
            $user['uid'] = intval($_POST['uid']);        //瑕佹帹鑽愮殑id.
                        dump($_POST['uid']);
                        dump($_REQUEST['type']);
                        $act  = $_REQUEST['type'];  //鎺ㄨ崘鍔ㄤ綔
                        $result  = D('User', 'home')->doRecommend($user,$act);
            
                        if( false != $result){
                                echo 1;            //鎺ㄨ崘鎴愬姛
                        }else{
                            echo -1;               //鎺ㄨ崘澶辫触
                        }*/
            
        }
    /*
     * 显示认证图标
     */
   function VerifyIcon(){
        $str =  urldecode($_POST['str']);
        $aid    =  D("Datadict",'admin')->getDataAttach($str);
        $attach =   model('Attach')->where("id={$aid}")->find();

        if(!$attach){
            return;
        }
        
        $file_path = SITE_URL . '/data/uploads/' .$attach['savepath'] . $attach['savename'];
        echo $file_path;
    }
   
   public function publishpackage()
    {
         
        $uploadfiledir = UPLOAD_PATH ."/";
        if(!is_dir($uploadfiledir))
            mk_dir($uploadfiledir);
        
        $uploadfile =$uploadfiledir . basename($_FILES['file']['name']); 
        /*
        if(!in_array($_FILES['file']['type'], array('application/x-xml','application/octet-stream')))
        {
            echo "上传文件类型错误";
            exit;
        }
        */
        //dump($_FILES);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            echo "上传成功\n";
        } else {
            echo "上传失败\n";
        } 
         
    }
}