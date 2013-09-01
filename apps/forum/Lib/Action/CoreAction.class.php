<?php
class CoreAction extends Action {

	var $_Core = array();			//用户存储核心数据
	var $topic;						//主题表
	var $post;						//回复表
	var $class;						//当前所在版块
	var $_topnav;
	var $cateService;				//分类服务
	var $popedom;					//权限服务
	var $mask;

	protected static $dontNeedInit = array('do/post','do/replay');			//不需要使用初始化的Action
	protected $TAG = "base";		//js引入key值

	//核心数据初始化
	public function _initialize() {
		$this->mask = $this->mid;
		$this->topic = D('Topic');
		$this->post = D('Post');

		$now = strtolower(MODULE_NAME."/".ACTION_NAME);
		if(in_array($now, self::$dontNeedInit)) {
			return false;
		}

		//获取论坛分类信息
		$this->cateService = service('Category');
		$cacheCategory = F('categoryListData');
		if($cacheCategory) {
			$this->_Core['category'] = $cacheCategory;
		} else {
			$this->_Core['category'] = $this->cateService->getCategoryList();
			F('categoryListData', $this->_Core['category']);
		}

		//判断用户是否拥有管理员权限 TODO 目前都返回true
		$this->_Core['isWebAdmin'] = model('UserGroup')->isAdmin($this->mid);
		$this->_Core['dict'] = model('Xdata')->lget("forum_dict");		//TODO
		$this->getHeaderRes();			//头部js选择器

		//站点信息
		$headerList = getHeaderLinkList();
//		$this->_Core['siteSetting'] = $this->site['forum_setting'];
		$this->_Core['siteSetting'] = model('Xdata')->get('forum:forum_setting');
		$this->_Core['xsite'] = $this->site['name'];

		$this->assign('Core', $this->_Core);
		$this->assign('mid', $this->mid);
		$this->assign('headerForum', $headerList);
	}

	protected function specialClassPopedom($userAction, $adminAction, $data, $other) {
		$this->boardPurview ();
//		$userGroup = getUserInfo ( $this->mid, service ( 'Cache' ) );
		$userGroup = $userGroup ['userGroup'];
		$admin = $this->_Core ['isAdmin'] || $this->_Core ['isWebAdmin'];
		if (! $admin) { //如果没有管理权限
			$this->coverPopedom ( $data ['postInfo'] ['tclass'] );
			if (! $admin && $data ['postInfo'] ['uid'] != $this->mid) {
				$this->error ( L ( 'do_no_purview', array ('$action' => "编辑" ) ) );
			}
			$this->checkPopedom ( $userAction );
		} else {
			$this->checkPopedom ( $adminAction );
		}
	}

	protected function getHeaderRes() {
		$this->assign ( 'jsType', $this->TAG );
	}

	public function coverPopedom($cate) {
		//覆盖页面上的权限检测
		if (! empty ( $cate )) {
			$catePopedom = F('cate_popedom_'.$cate);
			if(!$catePopedom){
				$cateMap ['id'] = intval ( $cate );
				$catePopedom = unserialize ( M ( 'forum_tclass' )->where ( $cateMap )->getField ( 'popedom' ) );
				if($catePopedom){
					F('cate_popedom_'.$cate,$catePopedom);
				}else{
					F('cate_popedom_'.$cate,"none");
				}
			}
			if($catePopedom )
				$this->popedom->coverPopedom ( $catePopedom );
		}
	}

	public function setHeader($fid, $logoId) {
		$this->assign ( 'forumUrl', U ( 'forum/List/index', array ('class' => $fid ) ) );
		if (C ( 'MEMCACHED_ON' )) {
			$cache = service ( 'Cache' );
			$url = $cache->getForumIcon($fid."_".$logoId);
			if(!$url){
				$attach = model ( 'Xattach' );
				$icon = $attach->where ( 'id=' . $logoId )->field ( "savepath,savename" )->find ();
				$url = SITE_URL . '/data/uploads/' . $icon ['savepath'] . $icon ['savename'] ;
				$cache->setForumIcon($fid.'_'.$logoId,$url);
			}else{
				$icon = true;
			}
		} else {
			$attach = model ( 'Xattach' );
			$icon = $attach->where ( 'id=' . $logoId )->field ( "savepath,savename" )->find ();
			$url = SITE_URL . '/data/uploads/' . $icon ['savepath'] . $icon ['savename'] ;
		}
		if ($icon) {
			$this->assign ( 'forumLogo',$url );
		}
	}

	//版块初始化
	protected function boardPurview($action = "allow_browser") {
		$purview = D('ForumSetting')->getBaseicSet($this->class, false, false);
		$purview['site'] = $this->site;

		$popedom = service('Popedom', array($this->class, $purview));
		$this->popedom = $popedom;
		$this->checkPopedom($action);
		$this->parseUrl();
		$this->setHeader($purview['fid'], $purview['forum_logo']);
		$this->assign("setting", $purview);
		$this->_Core['isAdmin'] = in_array($this->mid, $purview ['manager']) ? true : false;
		//管理组添加权限
		$forumGroup = M('forum_user_group')->field('gid')->where('type=1')->findAll();
		$forumGroup = getSubByKey($forumGroup, 'gid');
		$userGroup = M('user_group_link')->field('user_group_id')->where('uid='.$this->mid)->findAll();
		$userGroup = getSubByKey($userGroup, 'user_group_id');
		array_push($userGroup, 1);
		$intersect = array_intersect($forumGroup, $userGroup);
		if(!empty($intersect)) {
			$this->_Core['isAdmin'] = true;
		}

		$this->_Core['setting'] = $purview;
		$this->assign('fid', $this->class);
		$this->assign("popedom", $popedom);
		$this->assign('Core', $this->_Core);
	}

	//验证权限
	public function checkPopedom($action) {
		$rule = func_get_args();
		foreach($rule as $value) {
			if(!$this->popedom->check($value)) {
				if($value == 'allow_read') {
					$this->assign('jumpUrl', U('forum/Index/index'));
				}
				$this->error(L('do_no_purview', array('$action' => L($action))));
			}
		}
	}

	public function parseUrl() {
		$url = "class=" . $this->class;

		$this->assign ( 'url', U ( 'forum/List/index', $url ) );

		if (isset ( $_GET ['special'] )) {
			$url .= "&special=" . $_GET ['special'];
		}

		if (isset ( $_GET ['sign'] )) {
			$url .= "&sign=" . intval ( $_GET ['sign'] );
		}

		if (isset ( $_GET ['type'] )) {
			$url .= "&type=" . t ( $_GET ['type'] );
		}
		$url = U ( 'forum/List/index', $url );
		$postUrl = U ( 'forum/Index/post', "class=" . $this->class );
		$this->assign ( "signUrl", $url );
		$this->assign ( "postUrl", $postUrl );
	}

	//获取版块导航 - 面包屑
	protected function getTopNav($inputData) {
		$arg = func_get_args();
		$nowPath = $this->_getTopNav();
		if(!empty($nowPath)) {
			$this->assign('nowPath', $nowPath);
		} else {
			$this->assign('nowPath');
		}
	}

	//版块导航数据生成函数
	private function _getTopNav() {
		if(empty( $this->class)) {
			$this->assign('nowPath');
			return null;
		} else {
			$data = $this->cateService->getCategoryPath($this->class, false);
			$temp = array_shift($data);
			foreach($data as &$value) {
				$value ['url'] = U('forum/List/index', array('class' => $value['fid']));
				if($temp['fid'] == 406) {
					$value['title'] = $temp['name'].'('.$value['name'].')';
				} else {
					$value['title'] = $value['name'];
				}
				unset($value['fid']);
				unset($value['name']);
			}
			$result = array();
			foreach($data as $v) {
				$result[] = sprintf('<a href="%s">%s</a>', $v['url'], $v['title']);
			}
			$header = sprintf('<a href="%s">%s</a>', U('forum/Index/index'), '论坛');
			array_unshift($result, $header);
			return implode(' > ', $result);
		}
	}

	function getCategoryTree($class) {

	}

	protected function _getLeftAttentionData($add = true) {
		//获取关注数据
		$attentionData = service ( 'Follow' )->getFollowData ( $uid = $this->mid, $type = 'bbs' );

		if (! empty ( $attentionData )) {
			$tagDao = model ( 'Xtag' );
			foreach ( $attentionData as &$value ) {
				$cid = $value;
				$value = array ();
				if (! $add) {
					$title = explode ( ',', $tagDao->getTagName ( $cid ) );
					$value ['title'] = array_pop ( $title );
				} else {
					$value ['title'] = $tagDao->getTagName ( end ( explode ( ',', $cid ) ) );
				}
				$value ['cid'] = $cid;
			}
		}
		return $attentionData;
	}

	public function changeSign($ids){
		$d['attach_id'] = array('in',$ids);
		$result = M('forum_attach')->where($d)->field('tid')->group('tid')->findAll();
		$all = M('forum_attach')->where($d)->findAll();
		foreach ($all as $key=>$value){
			$post['tid'] = $value['tid'];
			if($value['pid'] == 0){
				$post['istopic'] = 1;
				$attachPost = M('forum_post')->where($post)->find();
			}else{
				$post['pid'] = $value['pid'];
				$attachPost = M('forum_post')->where($post)->find();
			}
			$postAttach = unserialize($attachPost['attach']);
			foreach($postAttach as $k=>$val){
				if(is_array($ids)){
					if(in_array($val['id'],$ids)){
						unset($postAttach[$k]);
					}
				}else{
					if($val['id'] == $ids){
						unset($postAttach[$k]);
					}
				}
			}
			$postData['attach'] = serialize($postAttach);
			M('forum_post')->where($post)->save($postData);
			unset($post);
		}
		$img = array('jpg','jpeg','png','gif','bmp');
		foreach($result as $key=>$value){
			$temp['tid'] = $value['tid'];
			$te['tid'] = $value['tid'];
			$te['attach_id'] = array('not in',$ids);
			$otherAttach = M('forum_attach')->where($te)->findAll();
			$topic = M('forum_topic')->where($temp)->find();
			$topicAttach = explode(',',$topic['sign']);
			//查看删除附件后，是否还存在其他附件
			if(empty($otherAttach)){
				foreach ($topicAttach as $k=>$val){
					if($val == 25 || $val == 27){
						unset($topicAttach[$k]);
					}
				}

				$s['sign'] = implode(',',$topicAttach['sign']);
				$topic = M('forum_topic')->where($temp)->save($s);
			}else{
				$a['id'] = array('in',getSubByKey($otherAttach,'attach_id'));
				$resultAttach = M('x_attach')->where($a)->findAll();
				foreach($resultAttach as $ke=>$v){
					if(in_array(strtolower($v['extension']),$img)){
						$hasImg = true;
						break;
					}
				}
				if(!$hasImg){
					foreach($topicAttach as $kk=>$vv){
						if($vv==25){
							$topicAttach[$kk] = 27;
						}
					}
					$ta['sign'] = implode(',',$topicAttach);
					M('forum_topic')->where($temp)->save($ta);
					$da['signId'] = 27;
					M('forum_sign_post')->where($temp)->save($da);
				}
			}
			if(C ( 'MEMCACHED_ON' )){
				$cache = service ( 'Cache' );
				$cache->cleanAttach ( $value['id'] );
				$cache->cleanDetail($value ['tid']);
				$cache->cleanForumList($this->class);
				$cache->cleanTopicData($value['tid']);
			}
		}
	}
	public  function changSign($ids) {
		//$d['pid'] = 0;
		$d['attach_id'] = array('in',$ids);
		$result = M('forum_attach')->where($d)->field('tid')->group('tid')->findAll();
		$img = array('jpg','jpeg','png','gif','bmp');
		foreach($result as $key=>$value){
			$post['tid'] = $value['tid'];
		//	$post['istopic'] = 1;
			$attachTopic = M('forum_post')->where($post)->find();
			$topic = M('forum_topic')->where('tid='.$value['tid'])->find();
			$topicAttach = explode(',',$topic['sign']);
			$attach = unserialize($attachTopic['attach']);
			foreach ($attach as $k=>$val){
				if(is_array($ids)){
					if(in_array($val['id'],$ids)){
						unset($attach[$k]);
					}
				}else{
					if($val['id'] == $ids){
						unset($attach[$k]);
					}
				}
			}
			if(empty($attach)){
				M('forum_sign_post')->where('tid='.$value['tid'])->delete();
				$ta['sign'] = '';
				M('forum_topic')->where('tid='.$value['tid'])->save($ta);
			}else{
				$att['id'] =array('in',getSubByKey($attach,'id')) ;
				$a = M('x_attach')->where($att)->findAll();
				foreach($a as $ke=>$v){
					if(in_array(strtolower($v['extension']),$img)){
						$hasImg = true;
						break;
					}
				}
				if($hasImg){

				}else{
					foreach($topicAttach as $kk=>$vv){
							if($vv==25){
								$topicAttach[$kk] = 27;
							}
						}
						$ta['sign'] = implode(',',$topicAttach);
						M('forum_topic')->where('tid='.$value['tid'])->save($ta);
						$da['signId'] = 27;
						M('forum_sign_post')->where('tid='.$value['tid'])->save($da);
				}

			}
			$at['attach'] = serialize($attach);
			M('forum_post')->where($post)->save($at);
		}
	}

	public function memCache($tid,$fid,$top=0){
			if (C ( 'MEMCACHED_ON' )) {
				$cache = service ( 'Cache' );
				$cache->cleanDetail($tid);
				$cache->cleanForumList($fid);
				$cache->cleanTopicData($tid);
				if($top == 2){
					$cache->cleanTopForumList();
				}
		}
	}
}
?>