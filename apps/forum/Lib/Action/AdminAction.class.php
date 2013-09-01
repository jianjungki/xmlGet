<?php
import('admin.Action.AdministratorAction');
class AdminAction extends AdministratorAction {
	
	private $cateService;					//分类服务
	private $defaultConfig = array();		//默认配置
	
	public function _initialize() {
		$_GET =	$this->_clean_data($_GET);
		//$_POST = $this->_clean_data($_POST);
	}
	
	//过滤值，不能有安全问题
	protected function _clean_data($data){
		if(is_array($data)) {
			foreach($data as $k => $v) {
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
	
	/*** 全局设置 ***/
	public function forumAllSetting() {
		$xdata = model('Xdata');
		$data = $xdata->get('forum:forum_setting');
		$this->assign($data);
		$this->display();
	}
	
	public function doAddFroumSetting() {
		if(empty($_POST)) {
			return;
		}
		$xdata = model ( 'Xdata' );
		
		$m['attach_auditing'] = intval($_POST['attach_auditing']);
		$m['attach_type'] = t($_POST['attach_type']);
		$m['attach_num'] = abs(intval($_POST['attach_num']));
		$key = "forum:forum_setting";
		$res = $xdata->get($key);
		if($res) {
			$result = $xdata->save($key, $m);
		} else {
			$result = $xdata->put($key, $m);
		}
		$m['url'] = U('forum/Admin/forumAllSetting');
		if($result) {
			addLog($this->mid, $m, 'add_forum_setting');
			$this->success('设置成功');
		}
	}
	
	/*** 模板设置 ***/
	public function index() {
		$this->cateService = service('Category');
		$cate = $this->cateService->getCategoryList();
		$root = $this->cateService->getRoot();
		$list = $this->replaceCate($cate, 0, $root);
		$this->assign('root', $root);
		$this->assign($_REQUEST);
		$this->assign('list', $list);
		$this->display();
	}
	
	private function replaceCate($list, $level = 0, $root) {
		$str = "";
		$level ++;
		$styleCount = 30 * $level;
		$style = 'style="padding-left: ' . $styleCount . 'px;"';
		foreach ( $list as $value ) {
			if (isset ( $value ['children'] )) {
				$str .= $this->replaceItem ( $value, $style, $level, true );
				$str .= '<div id="fc' . $value ['fid'] . '" class="sort">';
				$str .= $this->replaceCate ( $value ['children'], $level, $root );
				$str .= '</div>';
			} else {
				$str .= $this->replaceItem ( $value, $style, $level, false );
			}
		}
		return $str;
	}
	
	private function replaceItem($data, $style, $index, $hasChildren) {
		$addButton = '<a href="javascript:void(0)" onClick="addChildrenThread(' . $data ['fid'] . ')">添加</a>';
		if ($hasChildren) {
			$childrenImage = '<img src="../Public/images/aaOff.png" rel="off" width="9" height="9"
style="margin-right: 5px;_margin-top:10px;" onClick="foldCate(' . $data ['fid'] . ',$(this))">';
			$hasChildren = 1;
		} else {
			$changeParent = "<a href='javascript:void(0)' onclick='changeParent({$data['fid']})'>转移分区</a>";
			$hasChildren = 0;
		}
		$manage = empty ( $data ['forum_manager'] ) ? array () : explode ( ',', $data ['forum_manager'] );
		$manager = array ();
		$manage = array_unique ( array_filter ( $manage ) );
		foreach ( $manage as $value ) {
			$manager [] = getUserName ( $value );
		}
		
		$manager = implode ( ',', $manager );
		$url = U ( 'forum/admin/basicSet', "fid=" . $data ['fid'] );
		$manage = implode(',',$manage);
		$temp_html = <<<EOT
<div id="f{$data['fid']}" class="line" {$style}>
<div class="c1">{$childrenImage}{$data['name']}</div>
<div class="c2"><a href="javascript:void(0)" class="ico_top"><img align="absmiddle" style="_margin-top:8px;" src="__THEME__/images/zw_img.gif" onClick="sortCate({$data['fid']},1)"/></a>&nbsp;&nbsp;<a href="javascript:void(0)" onClick="sortCate({$data['fid']},0)"
class="ico_btm"><img align="absmiddle" style="_margin-top:8px;" src="__THEME__/images/zw_img.gif"/></a></div>
<div class="c3">{$addButton} <a href="javascript:void(0)" onclick="deleteCate({$data['fid']},{$hasChildren})">删除</a> <a href="{$url}">编辑</a> <a href="javascript:void(0)" onclick="setAdmin({$data['fid']},'版主设置','{$manage}')">版主设置</a> {$changeParent}</div><div class="c3">{$manager}</div><div class="clear"></div>
</div>
EOT;
		return $temp_html;
	}
	
	//设置版主
	public function setAdmin() {
		$fid = $_GET ['fid'];
		$type = "m";
		if (empty ( $_GET ['user'] )) {
			$tempMap ['fid'] = $fid;
			$data = D ( 'Forum' )->where ( $tempMap )->field ( 'forum_manager' )->find ();
			//取出本分类下的管理员
			$admin = $data ['forum_manager'];
		} else {
			$admin = $_GET ['user'];
		}
		//取出这个管理员的需要操作的信息
		$adminUid = strpos ( $admin, ',' ) === false ? $admin : explode ( ',', $admin );
		$map ['uid'] = array ('in', $adminUid );
		$user = D ( 'User' )->where ( $map )->field ( 'uid,uname,email' )->findAll ();
		//处理uname
		
		$cateTitle = service ( 'Category' )->getCategoryPath ( $fid );
		$cateTitle = $cateTitle ['path'];
		$temp = explode ( '>', $cateTitle );
		array_shift ( $temp );
		$cateTitle = implode ( ' > ', $temp );
		$this->assign ( 'type', $type );
		$this->assign ( 'fid', $fid );
		$this->assign ( 'userList', $user );
		$this->assign ( 'userId', $adminUid );
		$this->assign ( 'category', $cateTitle );
		$this->display ();
	}
	
	//获取转移分区信息
	public function changeParent(){
		$cate = service('Category');
		$this->assign('list',$cate->getDepthList(2,false));
		$this->assign('fid',intval($_GET['fid']));
		$this->display();
	}
	
	//执行转移分区操作
	public function doChangeParent(){
		$fid = intval($_REQUEST['fid']);
		$target = intval($_REQUEST['target']);
		$cate = Service('Category');
		if($cate->moveCategory($fid,$target)){
			$this->success("转移成功");
		}else{
			$this->error("转移失败");
		}
	}
	
	//验证分类信息
	public function checkCateIsLeaf() {
		$this->cateService = service ( "Category" );
		$fid = intval ( $_POST ['fid'] );
		
		if ($this->cateService->isLeafCategory ( $fid )) {
			echo 1;
		} else {
			echo - 1;
		}
	}
	
	//删除分组信息
	public function wind() {
		$this->cateService = service ( "Category" );
		$cate = $this->cateService->_format ( $this->cateService->getCategoryList () );
		$path = $this->cateService->getCategoryPath ( intval ( $_REQUEST ['fid'] ) );
		array_pop ( $path );
		array_shift ( $path );
		foreach ( $path as $value ) {
			$default [] = intval ( $value ['fid'] );
		}
		
		$map ['fid'] = intval ( $_REQUEST ['fid'] );
		$count = D ( 'Forum' )->getCount ( "thread_count", $map ['fid'] );
		
		$json = json_encode ( $cate );
		$firstCate = json_encode ( $default );
		$this->assign ( 'firstCate', $firstCate );
		$this->assign ( 'jeson', $json );
		$this->assign ( 'count', $count );
		$this->assign ( 'url', U ( 'forum/admin/deleteCate' ) );
		$this->display ();
	}
	
	//删除版块操作
	public function deleteCate() {
		$this->cateService = service ( "Category" );
		$fid = intval ( $_POST ['fid'] );
		$newFid = intval ( $_POST ['newFid'] );
		$type = intval ( $_POST ['type'] ) ? true : false;
		$m ['name'] = getBlockName ( $fid );
		$m ['url'] = U ( 'forum/Admin/index' );
		$result = D ( 'Forum' )->deleteForum ( $fid, $newFid, $type, $this->cateService );
		addLog ( $this->mid, $m, 'delete_cate' );
		echo $result;
	}
	
	//对左右值层级进行排序
	public function sortCate() {
		$fid = intval ( $_POST ['fid'] );
		$position = intval ( $_POST ['position'] );
		//求出同级的id
		$this->cateService = service ( "Category" );
		$type = $position?"up":"down";
		$res = $this->cateService->moveCategoryPlumb($fid,$type);
		echo $res;
	}
	
	//新增分区操作
	public function addThreadSetting() {
		if (empty ( $_POST )) {
			$this->success('您没有进行任何操作动作');
		}
		//检测版块名称是否为空
		$post = $_POST;
		if(isset($post['newThread'])) {
			foreach($post['newThread'] as $value) {
				foreach($value['name'] as $val) {
					$val = trim($val['name']);
					if(empty($val)) {
						$this->error('版块名称不能为空');
					}
				}
			}
		}
		
		$this->cateService = service ( "Category" );
		$data = $_POST ['newThread'];
		foreach ( $data as $pid => $value ) {
			foreach ( $value ['name'] as $k => $v ) {
				$name = $v;
				//添加新数据
				$newId = $this->cateService->addCategory ( $pid, $v );
				//修改该新字段的数据
				$needData = $this->cateService->getOneCategoryInfo ( $pid );
				$needData = $needData [0];
				unset ( $needData ['left_value'] );
				unset ( $needData ['right_value'] );
				unset ( $needData ['name'] );
				unset ( $needData ['fid'] );
				$map ['fid'] = $newId;
				$needData ['forum_icon'] = 0;
				$res = D ( 'Forum' )->where ( $map )->save ( $needData );
			}
		}
		$m ['name'] = $v;
		$m ['url'] = U ( 'forum/Admin/index' );
		if ($res !== false) {
			//清楚缓存
			addLog ( $this->mid, $m, 'add_thread_setting' );
			D('ForumSetting')->initBasicSet($newId);
			$this->success ( '添加成功' );
		} else {
			$this->error ( '添加失败' );
		}
	}
	
	//版块 - 编辑操作
	public function basicSet() {
		$gid = intval ( $_GET ['fid'] );
		$map ['fid'] = $gid;
		$baseic = D('ForumSetting')->getBaseicSet($gid,false,true);
		if (empty ( $baseic )) {
			$baseic = array ('image_state' => 1, 'dummy_state' => 1, 'truename_state' => 1, 'header_state' => 1, 'position' => 1, 'del_by_time' => 0 );
		}
		if(empty($baseic['timeSetting'])) {
			$baseic['timeSetting'] = model ( 'Xdata' )->get ( 'timeSetting:forum' );
		}else{
			$baseic ['timeSetting'] = unserialize ( $baseic ['timeSetting'] );
		}
		
		if(empty($baseic['attach_type'])){
//			$temp = model('Xdata')->get('site:attach_type');
			$temp = model('Xdata')->get('attach:attach_allow_extension');
//			$baseic['attach_type'] = $temp['attach_type'];
			$baseic['attach_type'] = $temp;
		}
		$this->assign ( 'fid', $gid );
		$this->assign ( 'forum',$baseic );
		$this->display ();
	}
	
	//提交模板的编辑内容
	public function doBasicSet() {
		if(empty($_POST)) {
			return;
		}
		$res = D('ForumSetting')->addBasicSet();
		$m ['name'] = getBlockName ( intval ( $_GET ['fid'] ) );
		$m ['url'] = U ( 'forum/Admin/basicSet&fid=' . intval ( $_GET ['fid'] ) );
		if (C ( 'MEMCACHED_ON' )){
			$cache = service('Cache');
			$cache->cleanTopNav(intval($_GET ['fid']) );
		}
		addLog ( $this->mid, $m, 'basic_set' );
		$this->success ( "设置成功" );
	}
	
	//版块 - 管理权限配置
	public function popedomSet() {
		$userRule = D ( 'UserRule' );
		$adminRule = D ( 'AdminRule' );
		$fid = intval ( $_GET ['fid'] );
		$admingids = M('forum_user_group')->where('type>0')->findAll();
		foreach($admingids as $key=>$value){
			$adminList[$value['gid']]= $adminRule->getPopedomOneGroup($fid,$value['gid']);
		}
		foreach($adminList as $key=>$value){
			foreach($admingids as $k=>$v){
				if($value['admingid'] == $v['gid']){
					$adminList[$key]['name'] = $v['name'];
					$adminList[$key]['gid'] = $v['gid'];
				}
			}
		}
		$gids = M('forum_user_group')->findAll();
		foreach($gids as $key=>$value){
			$userList[$value['gid']]= $userRule->getPopedomOneGroup($fid,$value['gid']);
		}
		foreach($userList as $key=>$value){
			foreach($gids as $k=>$v){
				if($value['usergid'] == $v['gid']){
					$userList[$key]['name'] = $v['name'];
					$userList[$key]['gid'] = $v['gid'];
				}
			}
		}
		$field = M ( 'forum_user_rule' )->getDbFields ();
		unset ( $field [0] );
		unset ( $field [1] );
		unset ( $field [7] );
		unset ( $field [10] );
		unset ( $field [12] );
		unset ( $field [13] );
		unset ( $field [14] );
		unset ( $field [15] );
		unset($field[18]);
		unset ( $field ['_autoinc'] );
		$this->assign ( 'type', t ( $_GET ['type'] ) );
		$this->assign ( 'fid', $fid );
		$this->assign('blockname',getBlockName($fid));
		$this->assign ( "userRule", $field );
		$this->assign ( 'userList', $userList );
		$this->assign ( 'adminList', $adminList );
		$this->display ();
	}
	
	//编辑版块权限
	public function doUserPope() {
		if(empty($_POST)) {
			return;
		}
		$fid = intval ( $_GET ['fid'] );
		$m ['name'] = getBlockName ( intval ( $_GET ['fid'] ) );
		$m ['url'] = U ( 'forum/Admin/popedomset&type=user&fid=' . $fid );
		$res = D('UserRule')->addUserPopedom ( $fid );
		addLog ( $this->mid, $m, 'user_pope');
		$this->success("设置成功");
	}
	
	//编辑版块中管理员权限
	 public function doAdminPope() {
		if (empty ( $_POST )) {
			return;
		}
		$res = D ( 'AdminRule' )->addAdminPope ( $_GET ['fid'] );
		$m ['name'] = getBlockName ( intval ( $_GET ['fid'] ) );
		$m ['url'] = U ( 'forum/Admin/popedomset&type=admin1&fid=' . intval ( $_GET ['fid'] ) );
		addLog ( $this->mid, $m, 'admin_pope' );
		$this->success ( "设置成功" );
	}
	
	//编辑版块中版块管理权限
	function doThreadPope() {
		if (empty ( $_POST ))
			return;
		$res = D ( 'AdminRule' )->addThreadPope ( $_GET ['fid'] );
		$m ['name'] = getBlockName ( intval ( $_GET ['fid'] ) );
		$m ['url'] = U ( 'forum/Admin/popedomset&type=admin2&fid=' . intval ( $_GET ['fid'] ) );
		addLog ( $this->mid, $m, 'thread_pope' );
		$this->success ( "设置成功" );
	}
	
	function doBlockPope() {
		if (empty ( $_POST ))
			return;
		$res = D ( 'AdminRule' )->addBlockPope ( $_GET ['fid'] );
		$m ['name'] = getBlockName ( intval ( $_GET ['fid'] ) );
		$m ['url'] = U ( 'forum/Admin/popedomset&type=admin3&fid=' . intval ( $_GET ['fid'] ) );
		addLog ( $this->mid, $m, 'block_pope' );
		$this->success ( "设置成功" );
	
	}
	
	//添加版块管理员
	public function addManage() {
		$fid = intval ( $_POST ['fid'] );
		$uid = $_POST ['uid'];
		$map ['fid'] = $fid;
		if(is_array($uid)){
			$save ['forum_manager'] = implode ( ',', $uid );
		}else{
			$save ['forum_manager'] = $uid;
		}
		$l = M ( 'forum' )->where ( $map )->field('forum_manager,name')->find ();
		$m ['name'] = $l ['name'];
		$m ['url'] = U ( 'forum/Admin/index' );
		addLog ( $this->mid, $m, 'add_manage' );
		
		$oldManager = explode(',',$l['forum_manager']);
		
		//设置为版主
		$result = D('Forum' )->where ( $map )->save ( $save );
		echo $result;exit;
		//TODO
		
		$userInfo = D ( 'SpaceInfo' );
		
		//设置用户组
		$cache = service ( "Cache" );
		
		$cache->cleanForumSetting ( $fid );
		
		$model = model ( 'UserGroup' );
		$uid = array_filter ( $uid );
		
		//添加权限设置
		foreach ( $uid as $value ) {
			$userMap = array ();
			if (! $model->inGroup ( $value, 2 )) {
				$map ['uid'] = $value;
				$map ['gid'] = 2;
				$ug = M ( 'forum_user_group' )->where ( 'gid=2' )->find ();
				$map ['gname'] = $ug ['name'];
				$res = $model->add ( $map );
				if (C ( 'MEMCACHED_ON' )) {
					$cache = service ( 'Cache' );
					$cache->cleanUserInfo($value);	
				}
			}
			$userMap ['uid'] = $value;
			$user = $userInfo->where ( $userMap )->field('managerForums')->find ();
			if ($user) {
				$managerForums = unserialize ( $user ['managerForums'] );
				$managerForums [] = $fid;
				$managerForums = array_filter(array_unique ( $managerForums ));
				$userSave ['managerForums'] = serialize ( $managerForums );
				$userInfo->where ( $userMap )->save ( $userSave );
			} else {
				$managerForums [] = $fid;
				$userMap ['managerForums'] = serialize ( $managerForums );
				$userInfo->add ( $userMap );
			}
		}
	}
	
	//默认管理权限
	public function setDefaultPope() {
		$this->_syncUserGroupInfo();
		
		$userRule = D ( 'UserRule' );
		$adminRule = D ( 'AdminRule' );
		$field = M ( 'forum_user_rule' )->getDbFields ();
		unset ( $field [0] );
		unset ( $field [1] );
		unset ( $field [7] );
		unset ( $field [10] );
		unset ( $field [12] );
		unset ( $field [13] );
		unset ( $field [14] );
		unset ( $field [15] );
		unset($field[18]);
		unset ( $field ['_autoinc'] );
		$userGroup  = M('forum_user_group')->findAll();
		
		foreach($userGroup as $key=>$value){
			$gid = $value['gid'];
			$l = M('forum_user_group')->query("select a.gid,a.name,b.* from ts_forum_user_group as a left join ts_forum_user_rule as b on b.usergid=a.gid where  b.fid=0 AND b.usergid=$gid");
			$userList[$key] = $l[0];
			if(empty($userList[$key])){
				$userList[$key]['gid'] = $gid;
				$userList[$key]['usergid'] = $value['gid'];
				$userList[$key]['name'] = $value['name'];
				$userList[$key]['fid'] = 0;
				foreach($field as $k=>$val){
					$userList[$key][$val] = 0;
				}
			}
		}
		$adminGroup = M('forum_user_group')->where('type>0')->findAll();
		$adminField = M ( 'forum_admin_rule' )->getDbFields ();
		unset ( $field [0] );
		unset ( $field [1] );
		unset ( $field ['_autoinc'] );
		foreach($adminGroup as $key=>$value){
			$gid = $value['gid'];
			$l = M('forum_user_group')->query("select a.gid,a.name,b.* from ts_forum_user_group as a left join ts_forum_admin_rule as b on b.admingid=a.gid where  b.fid=0 AND b.admingid=$gid AND a.type>0");
			$adminList[$key] = $l[0];
			if(empty($adminList[$key])){
				$adminList[$key]['gid'] = $gid;
				$adminList[$key]['admingid'] = $value['gid'];
				$adminList[$key]['name'] = $value['name'];
				$adminList[$key]['fid'] = 0;
				foreach($field as $k=>$val){
					$adminList[$key][$val] = 0;
				}
			}
		}
		$this->assign ( 'userRule', $field );
		$this->assign ( 'userList', $userList );
		$this->assign ( 'adminList', $adminList );
		$this->assign ( 'type', t ( $_GET ['type'] ) );
		$this->display ();
	}
	
	//论坛中设置用户组的所属
	public function setBelongsToGroup() {
		$this->_syncUserGroupInfo();
		$userGroup = M('forum_user_group')->findAll();
		$this->assign('userGroup', $userGroup);
		$this->display();
	}
	
	//论坛中设置用户组的所属操作
	public function doSetBelongsToGroup() {
		$userGroup = M('forum_user_group')->findAll();
		$adminGid = M('forum_admin_rule')->field('admingid')->findAll();
		$adminGid = getSubByKey($adminGid, 'admingid');
		$userGid = array();
		foreach($userGroup as $value) {
			$save['type'] = $_POST['usergroup_'.$value['gid']];
			$map['gid'] = $value['gid'];
			M('forum_user_group')->where($map)->save($save);
			if($_POST['usergroup_'.$value['gid']] == 1) {
				if(!in_array($value['gid'], $adminGid)) {
					$add['admingid'] = $value['gid'];
					M('forum_admin_rule')->add($add);
				}
				array_push($userGid, $value['gid']);
			}
		}
		//删除不存在的
		$delDiffGid = array_diff($adminGid, $userGid);
		unset($map);
		if(!empty($delDiffGid)) {
			$map['admingid'] = array('IN', $delDiffGid);
			M('forum_admin_rule')->where($map)->delete();
		}
		
		$this->success('设置成功');
	}
	
	//同步TS的用户组到论坛应用
	private function _syncUserGroupInfo() {
		$userGroup = M('user_group')->field('user_group_id AS gid, title AS `name`')->findAll();
		$validUserGroup = getSubByKey($userGroup, 'gid');
		$mapGroup['gid'] = array('NOT IN', '1,2');
		$userForumGroup = M('forum_user_group')->field('gid')->where($mapGroup)->findAll();
		$validUserForumGroup = getSubByKey($userForumGroup, 'gid');
		$addDiffGid = array_diff($validUserGroup, $validUserForumGroup);
		$delDiffGid = array_diff($validUserForumGroup, $validUserGroup);
		
		if(!empty($addDiffGid)) {
			$nameInfo = array();
			foreach($userGroup as $value) {
				$nameInfo[$value['gid']] = $value['name'];
			}
			foreach($addDiffGid as $value) {
				$add['gid'] = $value;
				$add['name'] = $nameInfo[$value];
				M('forum_user_group')->add($add);
				$addRule['usergid'] = $value; 
				M('forum_user_rule')->add($addRule);
			}
		}
		if(!empty($delDiffGid)) {
			$map['gid'] = array('IN', $delDiffGid);
			M('forum_user_group')->where($map)->delete();
			M('forum_user_rule')->where($map)->delete();
		}
	}
	
	//保存默认用户行为权限
	public function saveDeUser() {
		if (empty ( $_POST ))
			return;
		$res = D ( 'UserRule' )->addUserPopedom ( 0 );
		$m ['url'] = U ( 'forum/Admin/setDefaultPope&type=user' );
		addLog ( $this->mid, $m, 'save_de_user' );
		$this->success ( "设置成功" );
	}
	
	//保存的默认管理操作权限
	public function saveDeAdmin() {
		if (empty ( $_POST ))
			return;
		$res = D ( 'AdminRule' )->addAdminPope ( 0 );
		$m ['url'] = U ( 'forum/Admin/setDefaultPope&type=admin' );
		addLog ( $this->mid, $m, 'save_de_admin' );
		$this->success ( "设置成功" );
	}
	
	//保存管理面板权限
	public function saveBlockPope() {
		if (empty ( $_POST ))
			return;
		$res = D ( 'AdminRule' )->addBlockPope ( 0 );
		$m ['url'] = U ( 'forum/Admin/setDefaultPope&type=block' );
		addLog ( $this->mid, $m, 'save_block_pope' );
		$this->success ( "设置成功" );
	}
	
	//默认特殊贴决策
	public function specialPolicy() {
		$sign = D('Sign');
		$list = $sign->getAllSign(20);
		$count = $sign->count();
		$this->assign('count', $count);
		$this->assign('list', $list);
		$this->assign('signName', $sign->findAll());
		$this->display();
	}
	
	//新增默认特殊贴决策项
	public function addPolicy() {
		if (empty ( $_POST ))
			return false;
		$sign = D ( 'Sign', "forum" );
		if (! empty ( $_POST ['name'] ) && isset ( $_POST ['name'] )) {
			$map ['name'] = t ( $_POST ['name'] );
		} else {
			$this->error ( '名称不能为空' );
		}
		$name = $sign->where($map)->find();
		if($name){
			$this->error("此策略名已存在");
		}
		if (! empty ( $_POST ['attach'] )) {
			$map ['icon'] = array_shift ( explode ( '|', array_pop ( $_POST ['attach'] ) ) );
		} else {
			$this->error("策略图标不能 为空");
		}
		
		$map ['type'] = 1;
		$res = $sign->add ( $map );
		$m ['name'] = $map ['name'];
		$m ['url'] = U ( 'forum/Admin/specialPolicy' );
		if ($res) {
			addLog ( $this->mid, $m, 'add_policy' );
			$this->success ( "添加成功" );
		} else {
			$this->error ( "添加失败" );
		}
	}
	
	//编辑默认特殊贴策略项页面
	public function editPolicy() {
		$sign = D ( 'Sign', 'forum' );
		$signid = intval ( $_REQUEST ['signid'] );
		$list = $sign->where ( 'signid=' . $signid )->find ();
		$icon = model('Xattach')->where('id='.$list['icon'])->find();
		$list['icon'] = SITE_URL.'/data/uploads/'.$icon['savepath'].$icon['savename'];
		$usergroup = M('forum_user_group')->where('gid <> 2')->findAll();
		$this->assign('usergroup',$usergroup);
		$this->assign ( $list );
		$this->assign ( 'signName', $sign->findAll () );
		$this->display ();
	}
	
	//编辑默认特殊贴决策项
	public function doEditPolicy() {
		if (empty ( $_POST ))
			return;
		$signid = intval ( $_REQUEST ['signid'] );
		$sign = D ( 'Sign' );
		$map ['name'] = t ( $_POST ['name'] );
		$map['signid'] = array('neq',$signid);
		$name = $sign->where($map)->find();
		if(empty($map['name'])){
			$this->error("策略名不能为空");
		}
		if($name){
			$this->error("此策略名已存在");
		}
		if (! empty ( $_POST ['attach'] )) {
			$map ['icon'] = array_shift ( explode ( '|', array_pop ( $_POST ['attach'] ) ) );
		}
		$m ['url'] = U ( 'forum/Admin/editPolicy&signid=' . $signid );
		$sign->where ( 'signid=' . $signid )->save ( $map );
		
		addLog ( $this->mid, $m, 'edit_policy' );
		$this->assign('jumpUrl', U('forum/Admin/specialPolicy'));
		$this->success ( "编辑成功" );
	}
	
	//删除默认特殊贴决策贴
	public function delPolicy() {
		$sign = D ( 'Sign','forum' );
		$map ['signid'] = array ('in', $_POST ['signid'] );
		$type = $sign->where ( $map )->findAll ();
		foreach($type as $key=>$value){
			if($value['type'] == 0){
				$this->error("策略:".$value['name']."不可删除");
			}
		}
		$map['type'] = array('neq',0);
		$res = $sign->where ( $map )->delete ();
		if ($res) {
			addLog ( $this->mid, '', 'del_pope' );
			$this->success ( "删除成功" );
		} else {
			$this->error ( "删除失败" );
		}
	}
	
	//主题列表
	public function forumList() {
		$forumTopic = D ( 'ForumTopic' );
		$_GET = $this->_saveSearch ( $_GET );
		$data = $_GET;
		$forumSign = D ( 'forum_sign' )->findAll ();
		$map ['isdel'] = 0;
		if (! empty ( $_GET ['fid'] )) {
			$cate = Service ( "Category" );
			$fid = intval ( $_GET ['fid'] );
			if ($cate->isLeafCategory ( $fid )) {
				$map ['fid'] = $fid;
			} else {
				$fids = getSubByKey ( $cate->getSubCategoryList ( $fid ), 'fid' );
				$map ['fid'] = array ('in', $fids );
			}
		}
		if (! empty ( $_GET ['fromTime'] ))
			$stime = strtotime ( $_GET ['fromTime'] );
		if (! empty ( $_GET ['toTime'] ))
			$etime = strtotime ( $_GET ['toTime'] );
		if (! empty ( $_GET ['fviewcount'] ))
			$fview = intval ( $_GET ['fviewcount'] );
		if (! empty ( $_GET ['tviewcount'] ))
			$tview = intval ( $_GET ['tviewcount'] );
		if (! empty ( $_GET ['fReplyCount'] ))
			$freply = intval ( $_GET ['fReplyCount'] );
		if (! empty ( $_GET ['tReplyCount'] ))
			$treply = intval ( $_GET ['tReplyCount'] );
			//板块
		if (! empty ( $_GET ['block'] ))
			$map ['fid'] = $_GET ['block'];
			//标识
			if(isset ( $_GET ['signid'] ) && $_GET ['signid'] != 0&& in_array(intval($_GET ['signid']),array(1,2,3,4,5,10))){
			switch(intval($_GET ['signid'])){
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
		//作者
			//作者
		$name = t($_GET['username']);
		if (isset($name)) {
			$map ['maskName'] = array ('like', "%$name%");
		}
		//时间
		if (! empty ( $stime ) && ! empty ( $etime )) {
			$map ['cTime'] = array ('between', array ($stime, $etime ) );
		} elseif (! empty ( $stime ) && empty ( $etime )) {
			$map ['cTime'] = array ('between', array ($stime, time () ) );
		} elseif (empty ( $stime ) && ! empty ( $etime )) {
			$map ['cTime'] = array ('LT', time () );
		}
		//标题
		if (! empty ( $_GET ['title'] )) {
			$t = t ( $_GET ['title'] );
			$map ['title'] = array ('like', "%$t%" );
		}
		//点击次数
		if (! empty ( $fview ) && ! empty ( $tview )) {
			$map ['viewcount'] = array ('between', array ($fview, $tview ) );
		} elseif (! empty ( $fview ) && empty ( $tview )) {
			$map ['viewcount'] = array ('GT', $fview );
		} elseif (empty ( $fview ) && ! empty ( $tview )) {
			$map ['viewcount'] = array ('LT', $tview );
		}
		//多少天无回复
		if (! empty ( $_GET ['rdays'] )) {
			$temp = time () - intval ( $_GET ['rdays'] ) * 24 * 3600;
			$map ['cTime'] = array ('gt', $temp );
			$map['replycount'] = 0;
		}
		//回复次数
		if (! empty ( $freply ) && ! empty ( $treply )) {
			$map ['replycount'] = array ('between', array ($freply, $treply ) );
		} elseif (! empty ( $freply ) && empty ( $tview )) {
			$map ['replycount'] = array ('GT', $freply );
		} elseif (empty ( $freply ) && ! empty ( $treply )) {
			$map ['replycount'] = array ('LT', $treply );
		}
	
		if(isset ( $_GET ['signid'] ) && $_GET ['signid'] != 0&&!in_array(intval($_GET ['signid']),array(1,2,3,4,5,10))){
			
			$map['b.status'] = 1;
			if(intval($_GET['signid']) == 25 || intval($_GET['signid'])==27){
				$map['b.signId'] = array('in',array(25,27));
			}else{
				$map['b.signId'] = intval($_GET ['signid']);
			}
			$forumList = M('')->table("ts_forum_topic as a left join ts_forum_sign_post as b on a.tid = b.tid")->where($map)->order ( 'a.cTime DESC' )->findPage ( 15 );
		}else{
			$forumList = $forumTopic->where ( $map )->order ( 'cTime DESC' )->findPage ( 15 );
		}
		$this->assign ( $data );
		//$this->assign ( 'order', $order );
		$this->assign ( $forumList );
		$this->assign ( 'forumSign', $forumSign );
		$this->display ();
	}
	
	//保存搜索条件
	private function _saveSearch($map) {
		foreach($map as $key=>$value){
			$map[$key] = t($value);
		}
		if ($_POST) {
			Session::set ( 'userSearch', serialize ( $map ) );
		} elseif (isset ( $_GET ['p'] ) && Session::get ( 'userSearch' )) {
			$map = unserialize ( Session::get ( 'userSearch' ) );
		} else {
			unset ( $_SESSION ['userSearch'] );
		}
		return $map;
	}
	
	//选择版块弹窗信息
	public function selectBlockCate() {
		$c = Service ( 'Category' )->getCategoryList ();
		$s = Service ( 'Category' )->_format ( $c );
		$category = json_encode ( $s );
		$this->assign('sid',intval($_GET['id']));
		$this->assign ( 'category', $category );
		$this->display ();
	}
	
	//对帖子的操作
	public function doForum() {
		$forumTopic = D ( 'ForumTopic' );
		$forumid = is_array ( $_POST ['forumId'] ) ? implode ( ',', $_POST ['forumId'] ) : intval ( $_POST ['forumId']);
		$map ['tid'] = array ('in', explode ( ',', $forumid ) );
		if ($_POST ['type'] == 'del') {
			$res = $forumTopic->where ( $map )->setField ( 'isdel', 1 );
			M ( 'forum_commend' )->where ( $map )->delete ();
			$result = M ( 'forum_post' )->where ( $map )->setField ( 'isdel', 1 );
			$m ['num'] = $res;
			$m ['id'] = implode ( ',', $_POST ['forumId'] );
			$m ['url'] = U ( 'forum/Admin/forumList' );
			addLog ( $this->mid, $m, 'del_thread' );
		} elseif ($_POST ['type'] == 'add') {
			$res = $forumTopic->where ( $map )->setField ( 'isdel', 0 );
			$result = M ( 'forum_post' )->where ( $map )->setField ( 'isdel', 0 );
			$m ['num'] = $res;
			$m ['id'] = implode ( ',', $_POST ['forumId'] );
			$m ['url'] = U ( 'forum/Admin/recycle' );
			addLog ( $this->mid, $m, 'add_thread' );
		} elseif ($_POST ['type'] == 'delete') {
			
			$res = $forumTopic->where ( $map )->delete ();
			$result = M ( 'forum_post' )->where ( $map )->delete ();
			$m ['num'] = $res;
			$m ['id'] = implode ( ',', $_POST ['forumId'] );
			$m ['url'] = U ( 'forum/Admin/recycle' );
			addLog ( $this->mid, $m, 'delete_thread' );
		}
		echo $res;
	}
	
	//主题回收站
	public function recycle() {
		$_GET = $this->_saveSearch ( $_GET );
		$data = $_GET;
		$forumTopic = D ( 'ForumTopic' );
		$c = Service ( 'Category' )->getCategoryList ();
		$forumSign = D ( 'forum_sign' )->findAll ();
		$map ['isdel'] = 1;
		if (! empty ( $_GET ['fid'] )) {
			$cate = Service ( "Category" );
			$fid = intval ( $_GET ['fid'] );
			if ($cate->isLeafCategory ( $fid )) {
				$map ['fid'] = $fid;
			} else {
				$fids = getSubByKey ( $cate->getSubCategoryList ( $fid ), 'fid' );
				$map ['fid'] = array ('in', $fids );
			}
		}
		if (! empty ( $_GET ['fromTime'] ))
			$stime = strtotime ( $_GET ['fromTime'] );
		if (! empty ( $_GET ['toTime'] ))
			$etime = strtotime ( $_GET ['toTime'] );
		if (! empty ( $_GET ['fviewcount'] ))
			$fview = intval ( $_GET ['fviewcount'] );
		if (! empty ( $_GET ['tviewcount'] ))
			$tview = intval ( $_GET ['tviewcount'] );
		if (! empty ( $_GET ['fReplyCount'] ))
			$freply = intval ( $_GET ['fReplyCount'] );
		if (! empty ( $_GET ['tReplyCount'] ))
			$treply = intval ( $_GET ['tReplyCount'] );
			//板块
		if (! empty ( $_GET ['block'] ))
			$map ['fid'] = $_GET ['block'];
			//标识
		if(isset ( $_GET ['signid'] ) && $_GET ['signid'] != 0&& in_array(intval($_GET ['signid']),array(1,2,3,4,5,10))){
			switch(intval($_GET ['signid'])){
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
		//作者
			//作者
		$name = t($_GET['username']);
		if (isset($name)) {
			$map ['maskName'] = array ('like', "%$name%");
		}
		//时间
		if (! empty ( $stime ) && ! empty ( $etime )) {
			$map ['cTime'] = array ('between', array ($stime, $etime ) );
		} elseif (! empty ( $stime ) && empty ( $etime )) {
			$map ['cTime'] = array ('between', array ($stime, time () ) );
		} elseif (empty ( $stime ) && ! empty ( $etime )) {
			$map ['cTime'] = array ('LT', time () );
		}
		//标题
		if (! empty ( $_GET ['title'] )) {
			$t = t ( $_GET ['title'] );
			$map ['title'] = array ('like', "%$t%" );
		}
		//点击次数
		if (! empty ( $fview ) && ! empty ( $tview )) {
			$map ['viewcount'] = array ('between', array ($fview, $tview ) );
		} elseif (! empty ( $fview ) && empty ( $tview )) {
			$map ['viewcount'] = array ('GT', $fview );
		} elseif (empty ( $fview ) && ! empty ( $tview )) {
			$map ['viewcount'] = array ('LT', $tview );
		}
		//多少天无回复
		if (! empty ( $_GET ['rdays'] )) {
			$temp = time () - intval ( $_GET ['rdays'] ) * 24 * 3600;
			$map ['cTime'] = array ('gt', $temp );
			$map['replycount'] = 0;
		}
		//回复次数
		if (! empty ( $freply ) && ! empty ( $treply )) {
			$map ['viewcount'] = array ('between', array ($freply, $treply ) );
		} elseif (! empty ( $freply ) && empty ( $tview )) {
			$map ['viewcount'] = array ('GT', $freply );
		} elseif (empty ( $freply ) && ! empty ( $treply )) {
			$map ['viewcount'] = array ('LT', $treply );
		}
		
		if(isset ( $_GET ['signid'] ) && $_GET ['signid'] != 0 &&!in_array(intval($_GET ['signid']),array(1,2,3,4,5,10))){
			$map['b.status'] = 1;
			if(intval($_GET['signid']) == 25 || intval($_GET['signid'])==27){
				$map['b.signId'] = array('in',array(25,27));
			}else{
				$map['b.signId'] = intval($_GET ['signid']);
			}
			$forumList = M('')->table("ts_forum_topic as a left join ts_forum_sign_post as b on a.tid = b.tid")->where($map)->order ( 'a.rTime DESC' )->findPage ( 15 );

		}else{
			$forumList = $forumTopic->where ( $map )->order ( 'rTime DESC' )->findPage ( 15 );
		}
		//$forumList = $forumTopic->where ( $map )->order ( 'rTime DESC' )->findPage ( 15 );
		//	$this->assign ( 'order', $order );
		$this->assign ( $forumList );
		$this->assign ( $data );
		$this->assign ( 'forumSign', $forumSign );
		$this->display ();
	}
	
	//模板列表
	public function templateList() {
		$data = D ( 'Template' )->findAll ();
		
		$model = D('ForumSpecialTopic');
		foreach($data as &$value){
			$map['special'] = $value['id'];
			$temp = $model->where($map)->find();
			if($temp && $value['type'] == 1){
				$value['type'] = 2;
			}
		}
		
		$this->assign ( 'data', $data );
		$this->display ();
	}
	
	//创建新模板操作
	public function doAddTemplate() {
		$save ['name'] = t ( $_POST ['title'] );
		$count = D ( 'Template','forum' )->where($save)->find();
		if($count>0){
			$this->error("模板名已经存在");
		}
		if (empty ( $save ['name'] ))
			$this->error ( "新的模板名不能为空" );
		
		$res  = D ( 'Template' )->add ( $save );
		if($res){
			$this->success("添加成功");
		}
		$m ['name'] = $save ['name'];
		$m ['url'] = U ( 'forum/Admin/templateList' );
		addLog ( $this->mid, $m, 'add_template' );
	}
	
	//编辑模板操作页面
	public function templateEdit() {
		$id = intval ( $_GET ['id'] );
		$model = D ( 'ForumTemplateWidget' );
		$map ['id'] = $id;
		$data = D ( 'Template' )->where ( $map )->find ();
		$map ['id'] = array ('in', $data ['template'] );
		$oldTemplate = $data ['template'];
		$temp = $model->where ( $map )->findAll ();
		$templateTemp = explode ( ',', $data ['template'] );
		foreach ( $templateTemp as $value ) {
			foreach ( $temp as $v ) {
				if ($v ['id'] == $value) {
					$template [] = $v;
				}
			}
		}
		$data ['template'] = $template;
		
		$sign = D ( 'Sign' )->findAll ();
		$widget = $model->findAll ();
		$this->assign ( 'widget', $widget );
		$this->assign ( 'sign', $sign );
		$this->assign ( 'hasSelected', explode ( ',', $oldTemplate ) );
		$this->assign ( 'template', $data );
		$this->display ();
	}
	
	//编辑模板操作
	public function doEditTemplate() {
		$map ['id'] = intval ( $_POST ['id'] );
		$save ['icon'] = intval ( $_POST ['icon'] );
		$save ['template'] = implode ( ',', $_POST ['template'] );
		$save ['name'] = trim ( t ( $_POST ['name'] ) );
		$find['id'] = array('neq',$map['id']);
		$find['name'] = $save['name'];
		$count = D('Template','forum')->where($find)->count();
		if($count>0){
			$this->error("模板名已经存在");
		}
		if (empty ( $save ['name'] ))
			$this->error ( "修改失败，模板名不能为空" );
		D ( 'Template' )->where ( $map )->save ( $save );
		$m ['url'] = U ( 'forum/Admin/templateEdit&id=' . $map ['id'] );
		addLog ( $this->mid, $m, 'edit_template' );
		$jumpUrl = U ( 'forum/admin/templateList' );
		$this->assign ( "jumpUrl", $jumpUrl );
		$this->success ( "修改成功" );
	}
	
	//删除模板操作
	public function templateDel() {
		$tempId = $_POST ['templeId'];
		if (empty ( $tempId )) {
			$this->error("操作失败,没有选择模板");
			exit ();
		}
		$map ['id'] = array ('in', $tempId );
		$tempMap['special'] = $map['id'];
		if(D('ForumSpecialTopic')->where($tempMap)->find()){
			$this->error("操作失败,该模板下拥有帖子数据");
			exit();
		}
		$template = D ( 'Template' );
		
		$templateData = $template->where($map)->findAll();
		foreach($templateData as $value){
			if($value['type'] == 0){
				$this->error("操作失败,选择的模板中有系统默认模板");
				exit;
			}
		}
		
		$res = $template->where ( $map )->delete ();
		$m ['num'] = $res;
		$m ['id'] = implode ( ',', $tempId );
		$m ['url'] = U ( 'forum/Admin/templateList' );
		if ($res) {
			addLog ( $this->mid, $m, 'template_del' );
			$this->success("操作成功");
		} else{
			$this->error("未知数据操作错误");
		}
	}
	
	//选项列表
	public function widgetList() {
		$data = D ( 'ForumTemplateWidget' )->findAll ();
		$type = getWidgetType ();
		$this->assign ( 'type', $type );
		$this->assign ( 'data', $data );
		$this->display ();
	}
	
	//编辑选项页面
	public function widgetEdit() {
		$id = intval ( $_GET ['id'] );
		$model = D ( 'ForumTemplateWidget' );
		$map ['id'] = $id;
		$data ['widget'] = $model->where ( $map )->find ();
		$type = getWidgetType ();
		$this->assign ( 'type', $type );
		$this->assign ( 'widget', $data ['widget'] );
		$this->display ();
	}
	
	//编辑选项操作
	public function doEditWidget() {
		$map ['id'] = intval ( $_POST ['id'] );
		$list = D ( 'ForumTemplateWidget' )->where ( $map )->find ();
		$m ['name'] = $list ['label'];
		unset ( $_POST ['id'] );
		$save = $_POST;
		$find['label'] = t($save['label']);
		$find['id'] = array('neq',$map['id']);
		$count =  D ( 'ForumTemplateWidget' )->where($find)->count();
		$find['field'] = t($save['field']);
		unset($find['label']);
		$count1 =  D ( 'ForumTemplateWidget' )->where($find)->count();
		$save['data'] = $save['checkdata'];
		unset($save['checkdata']);
		if($count>0){
			$this->error("选项名称已存在");
		}
		if($count1>0){
			$this->error("变量名已存在");
		}
		if (empty ( $save ['label'] ))
			$this->error ( "选项名称为空" );
		if (empty ( $save ['field'] ))
			$this->error ( "变量为空" );
		switch ($save ['widget']) {
			case "radio" :
			case "select" :
			case "checkbox" :
				if (trim ( empty ( $save ['data'] ) ))
					$this->error ( "请填写选项数据" );
				break;
		}
		if(!isset($save['must'])) {
			$save['must'] = 0;
		}
		D ( 'ForumTemplateWidget' )->where ( $map )->save ( $save );
		$m ['url'] = U ( 'forum/Admin/editWidget&id=' . $map ['id'] );
		addLog ( $this->mid, $m, 'edit_widget' );
		$this->success ( "修改成功" );
	}
	
	//创建新选项界面
	public function addWidgetEdit() {
		$id = intval ( $_GET ['id'] );
		$type = getWidgetType ();
		$this->assign ( 'type', $type );
		$this->display ();
	}
	
	//创建新选项操作
	public function doAddWidget() {
		$save = $_POST;
		$lable['label'] = t($save['label']);
		$count = D ( 'ForumTemplateWidget' )->where($lable)->count();
		$m['field'] = t($save['field']);
		$count1 = D ( 'ForumTemplateWidget' )->where($m)->count();
		if($count>0){
			$this->error ( "选项名称已存在" );
		}
		if($count1>0){
			$this->error ( "变量名已存在" );
		}
		if (empty ( $save ['label'] ))
			$this->error ( "选项名称为空" );
		if (empty ( $save ['field'] ))
			$this->error ( "变量为空" );
		switch ($save ['widget']) {
			case "radio" :
			case "select" :
			case "checkbox" :
				if (trim ( empty ( $save ['data'] ) ))
					$this->error ( "请填写选项数据" );
				break;
		}
		D ( 'ForumTemplateWidget' )->add ( $save );
		$m ['name'] = $save ['label'];
		$m ['url'] = U ( 'forum/Admin/widgetList' );
		addLog ( $this->mid, $m, 'add_widget' );
		$jumpUrl = U ( 'forum/admin/widgetList' );
		$this->assign ( "jumpUrl", $jumpUrl );
		$this->success ( "添加成功" );
	}
	
	//删除新选项操作
	public function delOption() {
		$widgetId = $_POST ['widgetId'];
		if (empty ( $widgetId )) {
			echo 0;
			exit ();
		}
		$map ['id'] = array ('in', $widgetId );
		$template = M ( 'forum_template_widget' );
		$res = $template->where ( $map )->delete ();
		$m ['num'] = $res;
		$m ['id'] = implode ( ',', $widgetId );
		$m ['url'] = U ( 'forum/Admin/widgetList' );
		if ($res) {
			addLog ( $this->mid, $m, 'del_option' );
			echo $res;
		} else {
			echo 0;
		}
	}
}

?>