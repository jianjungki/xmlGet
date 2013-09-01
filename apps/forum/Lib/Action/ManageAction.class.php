<?php
/**
 * 版块管理
 * 
 */
class ManageAction extends CoreAction {
	
//	var $_Core = array (); 			//核心数据
//	var $topic; 					//主题表
//	var $post; 						//回复表
//	var $class; 					//当前所在版块
//	protected $TAG = "base";
	
	//初始化版块管理
	public function _initialize() {
		parent::_initialize ();
		$_GET =	$this->_clean_data($_GET);
		$_POST = $this->_clean_data($_POST);
		$this->topic = D('Topic');
		$tp = t($_POST['tp']);
		$this->class = intval($_REQUEST['class']);
		if(!empty($this->class)) {
			$this->boardPurview();
			$list['path'] = $this->cateService->getCategoryPath($this->class);			//获取路径
			if(!$list['path']['path']) {
				$this->error("该板块不存在");
			}
		}
		
		$cate = service('Category');
//		$m['uid'] = $this->mid;
//		$list = M('space_info')->where($m)->find();
		$allBlock = service ( "Category" )->getCategoryList ();
//		$myBlock  =  unserialize($list['managerForums']);
//		foreach($myBlock as $key=>$value){
//			if($cate->isLeafCategory ($value)){
//				$myAllBlock[] = $value;
//			}else{
//				$child = $cate->getSubCategoryList($value);
//				foreach($child as $k=>$v){
//					$myAllBlock[]=$v['fid'];
//				}
//			}
//		}
//		$myAllBlock = array_unique($myAllBlock);
		$this->assign('allBlock',$allBlock);
//		$this->assign('myAllBlock',$myAllBlock);
		$this->assign('fid', $this->class);
		$this->getTopNav();
		$this->site['title'] = L('base_title');
		$this->setTitle();
		
	}

	//过滤表单发送的数据 - 提高安全性
	protected function _clean_data($data) {
		if(is_array($data)) {
			foreach($data as $k=>$v) {
				if(is_array($v)) {
					$data[$k] =	$this->_clean_data($v);	
				} else {
					$data[$k] =	h($v);
				}
			}
		} else {
			$data =	h($data);
		}
		return $data;
	}
	
	public function checkSign($data,$topic,$where = "box"){
		$result = $data;
		$output = array();
		foreach($result as $key=>&$value){
			$output[$key] = 0;
			switch($key){
				case "allow_commend":
					foreach($topic['sign'] as $v){
						if($v['signid'] == 23) $output[$key] = 1;
					}
					break;
				case "allow_top5":
					$output[$key] = $topic['topX']?1:0;
					break;
				case "allow_close":
					$output[$key] = $topic['lock']?1:0;
					break;
				case "allow_tip":
 					$output[$key] = $topic['top'] == 1?1:0;
					break;
				case "allow_all_tip":
					$output[$key] = $topic['top'] == 2?1:0;
					break;
				case "allow_elite":
					$output[$key] = $topic['dist']?1:0;
					break;
				case "allow_hide":
					$output[$key] = $topic['hide']?1:0;
					break;
			}
		}
		return $output;
	}
	
	//版块初始化
	protected function boardPurview($action = null) {
		$purview = D ( 'ForumSetting' )->getBaseicSet ( $this->class );
		$popedom = service ( 'Popedom', array ($this->class, $purview ) );
		$this->popedom = $popedom;
		$this->parseUrl ();
		if(isset($action) && !empty($action)){
			$this->checkPopedom($action);
		}
		$this->setHeader ( $purview ['fid'], $purview ['logo'] );
		$this->assign ( "setting", $purview );
		$this->_Core ['isAdmin'] = in_array ( $this->mid, $purview ['manager'] ) ? true : false;
		if (! ($this->_Core ['isAdmin'] || $this->_Core ['isWebAdmin'])) {
			$this->error ( L ( 'do_no_purview', array ('$action' => "管理" ) ) );
		}
		$this->_Core ['setting'] = $purview;
		$this->assign ( 'fid', $this->class );
		$this->assign ( "popedom", $popedom );
		$this->assign ( 'Core', $this->_Core );
	}
	
	public function checkPopedom($action) {
		$rule = func_get_args ();
		
		foreach ( $rule as $value ) {
			if (! $this->popedom->check ( $value )) {
				$this->error ( L ( 'do_no_purview', array ('$action' => L ( $action ) ) ) );
			}
		}
	}
	
	/* 版块管理 - 主题管理 */
	public function index() {
		$this->checkPopedom("allow_adjust_popedom");
		$this->assign('jsType', "detail_admin");
		if(!empty($_POST)) {
			D('Log')->put($this->mid, $this->class, "进行了本版块帖子搜索");
		}
		
		$adminService = service("Admin", array($this->popedom));
		$topicAdmin = $adminService->getTopicAdmin();
		
		//添加删除按钮
		$topicAdmin['allow_delete_thread'] = array(
										"name"=>"删除",
										"alert"=>true,
										"popup"=>false,
										"url"=>"delete",
										);
		$topicAdminKey = implode(',', array_flip(array_keys($topicAdmin)));
										
		$_POST = $this->_saveSearch($_POST);
		$data = $_POST;
		$list['type'] = $_GET ['type'] ? t($_GET ['type']) : 'index';
		if($list['type'] == 'recycle') {
			$map['isdel'] = 1;
		} else {
			$map['isdel'] = 0;
		}
		/********* 只获取当前版块的主题 ********/
		//版块下专题分类
		$list['tclass'] = intval($_GET['tclass']);
		$m['tagid'] = intval($_REQUEST['class']);
		$list['tclasslist'] = D('forum_tclass')->where($m)->findAll();
		//获取贴子列表，依照分类查找，还需要进行操作
		
		$order = $_POST['order'] ? t($_POST['order']) : 'cTime DESC';
		$order = "top DESC, ".$order;
		$list['forumSign'] = D('forum_sign')->findAll();
		
		if(!empty($_POST['tclass']) && $_POST['tclass'] != 0) {
			$map ['tclass'] = t($_POST ['tclass']);
		}
		if(!empty($_POST['fromTime'])) {
			$stime = strtotime(t($_POST['fromTime']));
		}
		if(!empty($_POST['toTime'])) {
			$etime = strtotime(t($_POST['toTime']));
		}
		if(!empty($_POST['fviewcount'])) {
			$fview = intval($_POST['fviewcount']);
		}
		if(!empty($_POST['tviewcount'])) {
			$tview = intval($_POST['tviewcount']);
		}
		if(!empty($_POST['fReplyCount'])) {
			$freply = intval($_POST['fReplyCount']);
		}
		if(!empty($_POST['tReplyCount'])) {
			$treply = intval($_POST['tReplyCount']);
		}

		//作者
		$name = t($_POST['username']);
		//TODO
		if (isset($name)) {
			$map ['maskName'] = array ('like', "%$name%");
		}
		//处理时间条件
		if(isset($_POST['forumSign']) && $_POST['forumSign'] != 0) {
			if(!empty($stime) && !empty($etime)) {
				$map['a.cTime'] = array('BETWEEN', array($stime, $etime));
			} else if(!empty($stime) && empty($etime)) {
				$map['a.cTime'] = array('BETWEEN', array($stime, time()));
			} else if(empty($stime) && !empty($etime)) {
				$map['a.cTime'] = array('LT', $etime);
			}
		} else {
			if(!empty($stime) && !empty($etime)) {
				$map['cTime'] = array('BETWEEN', array($stime, $etime));
			} else if(!empty($stime) && empty($etime)) {
				$map['cTime'] = array('BETWEEN', array($stime, time()));
			} else if(empty($stime) && !empty($etime)) {
				$map['cTime'] = array('LT', $etime);
			}
		}
		//标题
		if(!empty($_POST ['title'])) {
			$t = t($_POST['title']);
			$map['title'] = array('LIKE', "%$t%");
		}
		//点击次数
		if (! empty ( $fview ) && ! empty ( $tview )) {
			$map ['viewcount'] = array ('between', array ($fview, $tview ) );
		} elseif (! empty ( $fview ) && empty ( $tview )) {
			$map ['viewcount'] = array ('EGT', $fview );
		} elseif (empty ( $fview ) && ! empty ( $tview )) {
			$map ['viewcount'] = array ('ELT', $tview );
		}
		//多少天内无回复的主题
		if (! empty ( $_POST ['rdays'] )) {
			$temp = time () - intval ( $_POST ['rdays'] ) * 24 * 3600;
			$map ['cTime'] = array ('gt', $temp );
			$map['replycount'] = 0;
		}
		//回复次数
		if (! empty ( $freply ) && ! empty ( $treply )) {
			$map ['replycount'] = array ('between', array ($freply, $treply ) );
		} elseif (! empty ( $freply ) && empty ( $tview )) {
			$map ['replycount'] = array ('EGT', $freply );
		} elseif (empty ( $freply ) && ! empty ( $treply )) {
			$map ['replycount'] = array ('ELT', $treply );
		}
		//标识
		if(isset ( $_POST ['forumSign'] ) && $_POST ['forumSign'] != 0&& in_array(intval($_POST ['forumSign']),array(1,2,3,4,5,10))){
			switch(intval($_POST ['forumSign'])) {
				case 1:
					$map['top'] = 2;
					break;
				case 2:
					$map['top']  = 1;
					break;
				case 3:
					$map['lock'] = 1;
					break;
				case 4:
					$map['hide'] = 1;
					break;
				case 5:
					$map['dist'] = 1;
					break;
				case 10:
					$map['topX'] = 1;
					break;
			}
		}
		//版块分类
		if(!empty($_POST['tclass'])){
			$map['tclass'] = intval($_POST['tclass']);
		}
		
		$map ['fid'] = intval ( $_REQUEST ['class'] );
		$order = isset($_GET['order'])? 
						$_GET['order']." DESC"
							: "rTime DESC";
		$order = "top DESC,".$order;
		$this->assign('search', $data);
		$this->assign('topicAdminKey', $topicAdminKey);
		$this->assign('topicAdmin', $topicAdmin);
		if(isset ( $_POST ['forumSign'] ) && $_POST ['forumSign'] != 0 && !in_array(intval($_POST ['forumSign']),array(1,2,3,4,5,10))){
			if(intval($_POST['forumSign']) == 25 || intval($_POST['forumSign'])==27){
				$map['b.signId'] = array('in',array(25,27));
			}else{
				$map['b.signId'] = intval($_POST ['forumSign']);
			}
			$map['b.status'] = 1;
			$list ['topic'] = M('')->table("ts_forum_topic as a left join ts_forum_sign_post as b on a.tid = b.tid")->where($map)->order ( 'a.cTime DESC' )->findPage ( 15 );
		}else{
			$list ['topic'] = M('forum_topic')->where( $map )->order ( 'cTime DESC' )->findPage ( 15 );
		}

		$list['topic']['data'] = parseTopic($list['topic']['data'],false);
		foreach($list['topic']['data'] as $key=>&$value){
			$value['title'] = D('FilterWord')->filter($value['title']);
		}
		$this->assign('fid', $map['fid']);
		$this->assign($list);
		$this->display();
	}
	
	//保存查询条件
	private function _saveSearch($map) {
		foreach($map as $key=>$value) {
			$map[$key] = t($value);
		}
		if($_POST) {
			Session::set('userSearch', serialize($map));
		} elseif (isset ( $_GET ['p'] ) && Session::get ( 'userSearch' )) {
			$map = unserialize ( Session::get ( 'userSearch' ) );
		} else {
			unset ( $_SESSION ['userSearch'] );
		}
		return $map;
	}
	
	
	function set() {
		$forumSetting = D ( 'ForumSetting' );
		$map ['fid'] = intval ( $_REQUEST ['class'] );
		if ($_POST) {
			$data ['rule'] = h ( $_POST ['rule'] );
			if ($forumSetting->where ( $map )->count ()) {
				$forumSetting->where ( $map )->data ( $data )->save ();
			} else {
				$forumSetting->add ( array_merge ( $map, $data ) );
			}
			$this->success ( L ( 'do_success' ) );
		} else {
			$this->assign ( 'list', $forumSetting->where ( $map )->find () );
			$this->assign ( 'class', $map ['fid'] );
			$this->display ();
		}
	}
	
	//版块管理 - 分类管理	
	public function tclass() {
		$fid = $this->class;
		$this->boardPurview("allow_tclass_manage");
		
		$list = M('forum_tclass')->where('tagid='.$fid)->order('ordernum ASC')->findAll();
		foreach($list as $key => $value) {
			$list[$key]['count'] = D('Topic')->where("fid=$fid AND tclass=".$value['id']." AND isdel=0")->count();
		}
		$this->assign('list', $list);
		$this->assign('fid', $fid);
		$this->display();
	}
	
	//权限管理弹窗数据
	public function classPepodom() {
		$tclass = intval($_REQUEST['id']);
		$map['id'] = $tclass;
		$classPopedom = M('forum_tclass')->where($map)->field('popedom, tagid')->find();
		$this->class = $classPopedom['tagid'];

		$this->boardPurview("allow_tclass_manage");
		
		if(!$classPopedom['popedom']) {
			$fid = $classPopedom ['tagid'];
			$userList = M()->query("SELECT a.gid, a.name, b.* FROM ".C('DB_PREFIX')."forum_user_group AS a LEFT JOIN ".C('DB_PREFIX')."forum_user_rule AS b ON a.gid=b.usergid WHERE b.fid={$fid}");
			if(empty($userList)) {
				$userList = M()->query("SELECT a.gid, a.name, b.* FROM ".C('DB_PREFIX')."forum_user_group AS a LEFT JOIN ".C('DB_PREFIX')."forum_user_rule AS b ON a.gid=b.usergid WHERE b.fid=0" );
			}
			$classPopedom = $userList;
		} else {
			$res = unserialize($classPopedom['popedom']);
			$userGroup = array_keys($res);
			$userGroupMap['gid'] = array('IN', $userGroup);
			$userGroup = M('forum_user_group')->where($userGroupMap)->field('gid, name')->findAll();
			foreach($userGroup as &$value) {
				if(isset($res[$value['gid']])) {
					$value = array_merge($value, $res[$value['gid']]);
				}
			}
			$classPopedom = $userGroup;
		}
		
		$field = M('forum_user_rule' )->getDbFields();
		unset($field[0]);
		unset($field[1]);
		unset($field[7]);
		unset($field[10]);
		unset($field[12]);
		unset($field[13]);
		unset($field[14]);
		unset($field[15]);
		unset($field[18]);
		unset($field['_autoinc']);
		$adminList = M('forum_user_group')->query("SELECT a.gid, a.name, b.* FROM ".C('DB_PREFIX')."forum_user_group AS a LEFT JOIN ".C('DB_PREFIX')."forum_admin_rule AS b ON a.gid=b.admingid WHERE b.fid=$fid AND a.type>0");
		if(empty($adminList)) {
			$adminList = M('forum_user_group')->query("SELECT a.gid, a.name, b.* FROM ".C('DB_PREFIX')."forum_user_group AS a LEFT JOIN ".C('DB_PREFIX')."forum_admin_rule AS b ON a.gid=b.admingid WHERE b.fid=0 AND a.type>0");
		}
		$this->assign('adminList', $adminList);
		$this->assign('id', $tclass);
		$this->assign("userRule", $field);
		$this->assign('userList', $classPopedom);
		$this->display();
	}
	
	//版块分类权限设置操作
	public function doCatePopedom() {
		$cid = intval($_GET['cid']);
		$gid = $_POST['gids'];
		if(empty($gid)) {
			return;		
		}
		
		$dbMap['id'] = $cid;
		$tclass = M('forum_tclass')->where($dbMap)->field('tagid')->find();
		$this->class = $tclass['tagid'];
		
		if(!empty($_POST)) {
			D('Log')->put($this->mid,$this->class,"本版块分类权限进行了调整");
		}
		$this->boardPurview("allow_tclass_manage");
		
		//获取用户组的用户组id TODO
		$userGid = D('ForumUserGroup')->field('gid')->findAll();
		$userGid = getSubByKey($userGid, "gid");
		
		$field = array_unique($_POST['field']);
		unset($_POST['gid']);
		unset($_POST['field']);
		$search = array();
		
		foreach ( $_POST as $k => $value ) {
			foreach ( $value as $k2 => $v ) {
				$search [$k2] [$k] = $v;
			}
		}
		foreach ( $gid as $value ) {
			if(!in_array($value,$userGid)) continue;
			$map = array ();
			foreach ( $field as $v ) {
				$map [$v] = intval ( isset ( $search [$value] [$v] ) );
			}
			$res [$value] = $map;
		}
		$res = M('forum_tclass')->where($dbMap)->save(array("popedom" => serialize($res)));
		$jumpUrl = U('forum/Manage/tclass',array("class"=>$tclass['tagid']));
		$this->assign('jumpUrl', $jumpUrl);
		$this->success("操作成功");
	}
	
	//帖子模板弹窗数据 - 获取所有的模板
	public function classTemplate() {
		$cid = intval($_GET['id']);
		$map['id'] = $cid; 
		$oldTemplate = M('forum_tclass')->where($map)->field('tagid,template')->find();
		$this->class = $oldTemplate['tagid'];
		$this->boardPurview("allow_tclass_manage");
		
		if(!empty($oldTemplate['template'])){
			$this->assign('selectedTemplate',$oldTemplate['template']);
		}	
		
		$templateMap['type'] = 1;
		$template = D('Template')->where($templateMap)->findAll();
		
		$this->assign('template',$template);
		$this->assign('id',$cid);
		$this->display();
	}
	
	//帖子模板设置操作
	public function doCateTemplate() {
		$cid = intval($_GET['cid']);
		$template = intval($_POST['template']);
		if($template != 0 && empty($template)) {
			return ;
		}
		if(empty($_POST)) {
			return;
		}
		$dbMap['id'] = $cid;
		
		//权限处理
		$tclass = M('forum_tclass')->where($dbMap)->field('tagid')->find();
		$this->class = $tclass['tagid'];
		if(!empty($_POST)) {
			D('Log')->put($this->mid, $this->class, "本版块分类所属模板进行了调整");
		}
		$this->boardPurview("allow_tclass_manage");
		$map['id'] = $cid;
		$save['template'] = $template;
		$res = M('forum_tclass')->where($map)->save($save);
		$jumpUrl = U('forum/Manage/tclass',array("class"=>$this->class));
		$this->assign('jumpUrl', $jumpUrl);
		$this->success("操作成功");
	}
	
	/*** 版块管理 - 热门推荐 ***/
	public function commend() {
		$this->boardPurview("allow_commend_popedom");
		
		$sql = "SELECT topic.*,commend.id AS commendId
				FROM ".C('DB_PREFIX')."forum_commend AS commend
				LEFT JOIN ".C('DB_PREFIX')."forum_topic AS topic
				ON commend.tid = topic.tid
				ORDER BY commend.ordernum ASC";
		$top = M()->query($sql);
		if($top) {
			$top = parseTopic($top, true, true);
		}
		
		$this->assign('list', $top);
		$this->display();
	}
	
	//对热门推荐的帖子进行排序操作
	public function doCommend() {
		$this->boardPurview("allow_commend_popedom");
		
		if(empty($_POST['commendId'])) {
			return false;
		}
		
		if($_POST['type'] == 'saveCommendOrder') {
			$model = D('Commend');
			foreach($_POST['commendId'] as $key => $value) {
				$map['id'] = $value;
				$save['ordernum'] = $key;
				$model->where($map)->save($save);
			}
		}
		$jumpUrl = U('forum/Manage/commend', array('class' => $this->class));
		$this->assign('jumpUrl', $jumpUrl);
		$this->success("操作成功");
	}
	
	//版块分类操作 - 保存与删除
	public function dotclass() {
		$fid = intval($_REQUEST ['class']);
		if(empty($_POST)) {
			return;
		}
		$this->class = $fid;		
		$this->boardPurview("allow_tclass_manage");
		
		if(!empty($_POST)) {
			D('Log')->put($this->mid,$this->class,"本版块分类进行了调整");
		}
		
		switch($_POST['type']) {
			case 'saveCategory':
				D('Board')->savetClass($this->class, array_map("t", $_POST['newaddname']), array_map("t", $_POST ['name']), $_POST ['ordernum']);
				$this->success ("操作成功");
				break;
			case 'deleteCategory':
				$id = intval($_POST['id']);
				$result = M('forum_tclass')->where("id=$id AND tagid=$fid")->delete();
				if($result) {
					$this->success ("操作成功");
				} else {
					$this->error ("操作失败");
				}
				break;
		}
	}
	
	public function adminPopup() {
		$this->cateService = service ( 'Category' );
		$cate = $this->cateService->_format ( $this->cateService->getCategoryList () );
		$data ['json'] = json_encode ( $cate );
		$this->assign ( 'class', $_GET ['fid'] );
		$this->assign ( $data );
		$this->display ();
	}
	
	/*** 版块管理 - 公告管理 ***/
	public function affiche() {
		$this->boardPurview("allow_edit_report");

		$affiche = M()->Table(C('DB_PREFIX')."forum_affiche AS affiche LEFT JOIN ".C('DB_PREFIX')."forum_topic AS topic ON topic.tid = affiche.tid")
					  ->where("topic.isdel = 0 AND topic.affiche = 1")
					  ->order('affiche.ordernum ASC')
					  ->findAll();
		
		$affiche = parseTopic($affiche, false);
		$this->assign('list', $affiche);
		$this->display();
	}
	
	//对版块中的公告进行排序操作
	public function doAffiche() {
		$this->boardPurview("allow_edit_report");
		if(empty($_POST['afficheId'])) {
			return false;
		}
		
		if($_POST['type'] == 'saveAfficheOrder') {
			$model = D('ForumAffiche');
			foreach($_POST['afficheId'] as $key => $value) {
				$map['id'] = $value;
				$save['ordernum'] = $key;
				$model->where($map)->save($save);
			}
		}
		$jumpUrl = U('forum/Manage/affiche', array('class' => $this->class)) ;
		$this->assign('jumpUrl', $jumpUrl);
		$this->success("操作成功");
	}
	
	/*** 版块管理 - 日志管理 ***/
	public function log() {
		$fid = intval($_GET['class']);
		$this->class = $fid;
		$this->checkPopedom ( "allow_log_manage" );
		$data = D('Log')->where('fid='.$this->class)->order('cTime DESC')->findPage(20);
		$this->assign($data);
		$this->display();
	}
	
	/*** 版块管理 - 附件审核 ***/
	public function attach() {
		$this->assign('jsType', "detail_base");
		$this->checkPopedom("allow_check_attach");
		
		$list['type'] = $_GET['type'] ? $_GET ['type'] : 'nocheck';
		if($list['type'] == 'nocheck') {
			$map['b.status'] = 0;
		} else {
			$map['b.status'] = 1;
		}
		
		$_POST = $this->_saveSearch($_POST);
		$data = $_POST;
		//配置查询条件
		$fid = intval($_REQUEST ['class']);
		if(!empty($_POST['name']) || $_POST['name'] === '0') {
			$map['b.name'] = array('LIKE', '%'.t($_POST['name']).'%');
		}
		if(!empty($_POST['maskname'])) {
			$map ['c.maskName'] = array('like', '%'.t($_POST['maskname']).'%');
		}
		$this->assign('search', $data);
		$map['attach_type'] = 'forum_'.$fid;
		$map['b.isDel'] = 0;
		$map['c.isdel'] = 0;
		$list = M('')->Table(C('DB_PREFIX')."forum_attach AS a LEFT JOIN ".C("DB_PREFIX")."attach AS b ON a.attach_id=b.id LEFT JOIN ".C('DB_PREFIX')."forum_topic AS c ON a.tid=c.tid")
					 ->field('a.*,b.*,c.title,c.maskName')
					 ->where($map)
					 ->order('b.status,b.uploadTime DESC')
					 ->order('b.uploadTime DESC')
					 ->findPage(10);

//		foreach($list['data'] as $key=>&$value){
//			$value['name'] = D('FilterWord','forum')->filter($value['name']);
//			$value['title'] = D('FilterWord','forum')->filter($value['title']);
//		}
		$this->assign('fid',$fid);
		$this->assign($list);
		$this->assign('type',$list['type']);
		$this->display();
	}
	
	//论坛附件审核操作
	public function doAttachAudit() {
		$ids = $_POST['id'] ;
		$map['id'] = array('IN', $ids);
		$log['fileId'] = $_POST['id'];
		$res = M('attach')->where($map)->findAll();
		$type = getSubByKey($res, 'attach_type');
		list($type, $a) = explode('_', $type[0]);
		$this->class = intval($a);
		
		$this->boardPurview();
		$this->checkPopedom( "allow_check_attach" );
		$data['status'] = intval($_POST['status']) == 0 ? 1 : 0;
		$data['auditUid'] = $this->mid;
		if(!empty($_POST) && $data['status']==1) {
			D('Log')->addLog($this->mid, $this->class, "审核了本版块下的附件", $log);
		} else if (!empty($_POST) && $data['status'] == 0) {
			D('Log')->addLog($this->mid, $this->class, "取消了本版块下的附件的审核", $log);
		}
		if($res) {
			$res = M('attach')->where($map)->save($data);
		}
		if($res) {
			echo $res;
		}else{
			echo 0;
		}
	}
	
	//论坛附件删除操作
	public function doDelAttach() {
		$ids = $_POST['id'] ;
		$map['id'] = array('IN', $ids);
		$res = M('attach')->where($map)->findAll();
		$type = getSubByKey($res, 'attach_type');
		list($type, $a) = explode('_', $type[0]);
		$this->class = intval($a);
		$this->boardPurview();
		$this->checkPopedom( "allow_check_attach");
		$log['fileId'] = $_POST['id'];
		if(!empty($_POST)) {
			D('Log')->put($this->mid, $this->class, "删除了附件", $log);
		}
		$res = M('attach')->where($map)->setField('isDel', 1);
		
		$this->changeSign($ids);
		$attach['attach_id'] = array('IN', $ids);
		$result = M('forum_attach')->where($attach)->delete();
		if ($res) {
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
	/******************************************/
	public function addUserAction(){
		$userService = Service ('UserAction' );
		$res = $userService->storage ();
		echo $res;
	}
}
?>