<?php
class IndexAction extends CoreAction {
	
	var $postlimit = 20; //多少个回复算一页
	
	//论坛首页
	public function index() {
		$this->assign('isAdmin',model('UserGroup')->isAdmin($this->mid));
		$list = $this->cateService->getDepthList ( 3, false );
		$topicCountSql = 'SELECT count(1) as count,fid FROM ts_forum_topic where isdel = 0 and (top = 0 or top = 1)  group by fid';
		$postCountSql = 'SELECT count(1) as count,fid FROM ts_forum_post where isdel = 0 and istopic=0 group by fid';
		
		$topicCount = M()->query($topicCountSql);
		$postCount = M()->query($postCountSql);
		
		//处理图标
		$icoList = getSubByKey ( $list, 'forum_icon' );
		$icoList = array_filter ( $icoList );
		$attach = model ( 'Xattach' );
		$map ['id'] = array ('in', $icoList );
		$icon = $attach->where ( $map )->field ( "id,savepath,savename" )->findAll ();
		$iconList = array ();
		foreach ( $icon as $value ) {
			$iconList [$value ['id']] = SITE_URL . '/data/uploads/' . $value ['savepath'] . $value ['savename'];
		}
		foreach ( $list as $key=>$value ) {
			if (isset ( $iconList [$value ['forum_icon']] )) {
				$list[$key]['forum_icon'] = $iconList [$value ['forum_icon']];
			} else {
				$list[$key]['forum_icon'] = SITE_URL.'/apps/forum/Tpl/default/Public/images/forum_icon.png';
			}
			
			foreach($topicCount as $v){
				if($v['fid'] == $value['fid']){
					$list[$key]['t_count'] = $v['count']?$v['count']:0;
				}
			}
			foreach($postCount as $v){
				if($v['fid'] == $value['fid']){
					$list[$key]['p_count'] = $v['count']?$v['count']:0;
				}
			}
		}

		//获取论坛公告帖子
		$afficheInfo = M()->Table('`'.C('DB_PREFIX').'forum_affiche` AS a LEFT JOIN `'.C('DB_PREFIX').'forum_topic` AS t ON a.tid = t.tid ')
						  ->field('t.tid, t.title')
						  ->where('t.isdel = 0')
						  ->limit(10)
						  ->order('a.ordernum ASC')
						  ->findAll();
						  
		$this->assign('afficheInfo', $afficheInfo);
		
		$list = $this->cateService->treeFormat ( $list );
		$this->site ['title'] = L ( 'base_title' );
		$this->setTitle ();
		$this->assign ( "list", $list );
		$this->display ();
	}
	
	function forumsub(){
		$this->display();
	}
	
	function special(){
		$this->class = intval($_GET['class']);
		$this->getTopNav();
		$forum ['defaultCategory'] = $this->cateService->checkNotSecond ( $this->class );
		$forum ['tclasslist'] = D ( 'Board' )->gettClass ( $this->class );
		switch($_GET['special']){
			case 1:
				$this->boardPurview (); //加载权限控制器
				$this->coverPopedom($_GET['cate']);
				$this->checkPopedom("allow_exalt");
				$this->affiche();
				break;
			case 2:
				$this->boardPurview (  ); //加载权限控制器
				$this->coverPopedom($_GET['cate']);
				$this->checkPopedom("allow_post_vote");
				$this->vote();
				break;
			default:
				//获取该特殊帖下的所有模板
				$this->boardPurview ( ); //加载权限控制器
				$this->coverPopedom($_GET['cate']);
				$this->checkPopedom( "allow_post");
				
				$map['id'] = intval($_GET['special']);
				$template = D('Template')->where($map)->find();
				if(!$template) $this->error("您正在越权操作");
				$this->boardPurview ( "allow_post" ); //加载权限控制器
				$map['id'] = array('in',$template['template']);
				$widget = D('ForumTemplateWidget')->where($map)->findAll();
				$newInput = array();
				$tempWidget = explode(',',$template['template']);
				foreach($tempWidget as $v){
					foreach($widget as $value){
						if($value['id'] == $v){
							$temp['widget'] = $value;
							$newInput[] = W("Template".ucfirst($value['widget']),$temp,true);
						}
					}
				}
				$this->assign('special',$newInput);
				$this->assign('specialId',$_GET['special']);
				$this->assign ( $forum );
				$this->display('special');
		}
	}
	
	
	function _getOrder() {
//		$field = array ('rTime', 'cTime', 'viewcount', 'replycount' );
//		
//		$order = $_REQUEST ['order'];
//		if (! in_array ( $order, $field ))
//			$order = 'rTime';
//		$this->assign ( 'order', $order );
//		
//		$key = 'val_' . $order;
//		foreach ( $field as $v ) {
//			if ($v != $order) {
//				$this->assign ( 'val_' . $v, 'desc' );
//			} else {
//				$res = $_REQUEST [$key];
//				if (empty ( $res ))
//					$res = 'desc';
//				$val = $res == 'asc' ? 'desc' : 'asc';
//				$this->assign ( $key, $val );
//			}
//		}
//		
//		return $order . ' ' . $res;
	}
	
	function all() {
		$map ['isdel'] = 0;
		//精华贴
		if ($_GET ['dist'] == '1') {
			$map ['dist'] = 1;
		}
		$list ['isdist'] = intval ( $_GET ['dist'] );
		
		//获取贴子列表，依照分类列表
		$strOrder = $this->_getOrder ();
		
		$list ['topic'] = $this->topic->where ( $map )->field ( '*' )->order ( $strOrder )->findpage ( 40 );
		$this->assign ( $list );
		$this->setTitle ( '全部帖子 - 论坛' );
		$this->display ();
	}
	
	//订阅
	public function attention() {
		$attentionData = $this->_getAttentionData ();
		$count = count ( $attentionData );
		$cate = model ( 'Xcate' )->getTree ( 'bbs', '0' );
		$json = json_encode ( $cate );
		
		$firstCate = sprintf ( '["%s","%s"]', $cate [0] ['a'], $cate [0] ['d'] [0] ['a'] );
		$this->assign ( 'firstCate', $firstCate );
		$this->assign ( 'attentionCount', $count );
		$this->assign ( 'jeson', $json );
		$this->assign ( 'attentionData', $attentionData );
		$this->display ();
	}
	
	protected function _getAttentionData($add = true) {
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
					$value ['title'] = str_replace ( ',', ' > ', $tagDao->getTagName ( $cid ) );
				}
				$value ['cid'] = $cid;
			}
		}
		return $attentionData;
	}
	
	//发表新主题
	function post() {
		$this->class = intval($_GET['class']);
		$this->assign("jsType","editer");
		if(isset($_GET['cate'])){
			$cid = intval($_GET['cate']);
			$map['id'] = $cid;
			$template = M('forum_tclass')->where($map)->field('popedom,template')->find();
			if($template['template'] != 0 || !empty($template['template'])){
				$_GET['special'] = intval($template['template']);
				$this->special();
				exit;
			}else{
				$this->boardPurview (); //加载权限控制器
				$this->coverPopedom($_GET['cate']);
				$this->checkPopedom( "allow_post");
			}
		}else{
			$this->boardPurview ("allow_post"); //加载权限控制器
		}
		//覆盖页面上的用户行为权限检测
		$this->getTopNav();
		$forum ['defaultCategory'] = $this->cateService->checkNotSecond ( $this->class );
		$forum ['tclasslist'] = D ( 'Board' )->gettClass ( $this->class );
		$this->assign ( $forum );
		$this->display ();
	}
	
	//TODO 临时发表新公告
	function affiche() {
		$this->assign("jsType","editer");
		$forum ['defaultCategory'] = $this->cateService->checkNotSecond ( $this->class );
		$forum ['tclasslist'] = D ( 'Board' )->gettClass ( $this->class );
		$this->assign ( $forum );
		$this->display ('affiche');
	}
	//TODO 临时发表新投票
	function vote() {
		$this->assign("jsType","editer");
		$forum ['defaultCategory'] = $this->cateService->checkNotSecond ( $this->class );
		$forum ['tclasslist'] = D ( 'Board' )->gettClass ( $this->class );
		$this->assign ( $forum );
		$this->display ('vote');
	}
	
	//抄送
	function transcribe() {
		$uids = trim ( $_GET ['uids'] );
		$uids = strpos ( $uids, ',' ) === false ? $uids : explode ( ',', $uids );
		$map ['uid'] = array ('in', $uids );
		$user = D ( 'User' )->where ( $map )->field ( 'fullname,uid,uname' )->findAll ();
		$this->assign ( 'userList', $user );
		$this->assign ( 'userId', $uids );
		$this->display ();
	}
	
	function detail() {
		$id = intval ( $_GET ['id'] );
		$forum ['limit'] = $this->postlimit;
		$forum ['topic'] = $this->topic->getTopic ( $id );
		if (! $forum ['topic'])
			$this->error ( L ( '1001' ) );
		
		$this->class = $forum ['topic'] ['fid'];
		$this->boardPurview (); //载入权限控制器
		exit ();
		//dump($this->_Core );
		$map ['tid'] = $forum ['topic'] ['id'];
		if ($_GET ['auther'] == 1)
			$map ['uid'] = $forum ['topic'] ['uid'];
		$forum ['isReadAuther'] = intval ( $_GET ['auther'] );
		$this->getTopNav ( $this->class ); //获取导航;
		$forum ['list'] = $this->post->where ( $map )->order ( 'id ASC' )->findPage ( $forum ['limit'] );
		
		//获取用户等级、积分、经验、发贴、回复
		//		$arrUids = getSubByKey( $forum['list']['data'] , 'uid' );
		//		$uidsMap['uid'] = array('in',$arrUids);
		//		$arrUids = M('space')->where($uidsMap)->field('uid,credit_score,credit_exp')->findAll();
		$highighter = service ( 'Highlighter' ); //高亮显示
		

		foreach ( $forum ['list'] ['data'] as $key => $value ) {
			$forum ['list'] ['data'] [$key] ['attach'] = unserialize ( $forum ['list'] ['data'] [$key] ['attach'] );
			$forum ['list'] ['data'] [$key] ['content'] = $highighter->syntax ( $forum ['list'] ['data'] [$key] ['content'] );
		}
		
		//		//记录用户浏览时间
		$array = ($_SESSION ['visit_bbs_topic']) ? $_SESSION ['visit_bbs_topic'] : array ();
		if (! in_array ( $forum ['topic'] ['id'], $array )) {
			$this->topic->setInc ( 'viewcount', 'id=' . $forum ['topic'] ['id'] );
			array_push ( $array, $forum ['topic'] ['id'] );
			$_SESSION ['visit_bbs_topic'] = $array;
		}
		$forum ['currentUrl'] = U ( 'bbs/Index/detail', array ('id' => $id ) );
		//$forum['sharedata']['title']  = 'board:'.implode( " >> ", $this->getTopNav( $this->class , true ) );
		$this->assign ( $forum );
		$this->site ['title'] = $forum ['topic'] ['title'] . ' - ' . L ( 'base_forum' );
		$this->setTitle ();
		$this->display ();
	}
	
	//跳转至主题某页某个位置
	function gopost() {
//		$userSetting = D('Space','home')->getUserSetting($this->mid);
		$limit = $userSetting['postNum'];
		$intTid = intval ( $_GET ['id'] );
		$intPid = intval ( $_GET ['pid'] );
		
		$page = $this->post->where ( "tid=$intTid AND pid < $intPid" )->count ();
		$page = intval ( $page / ($limit) + 1 );
		redirect ( U ( 'forum/Detail/index', array ('id' => $intTid, 'p' => $page ) ) . "#p" . $intPid );
	}
	
	//帖子回复显示页面
	public function reply() {
		$this->assign("jsType", "editer");
		$tid = isset($_GET['id']) ? intval ( $_GET ['id'] ) : 0;
		$topic = $this->post->getPostInfo ( $tid, true );
		
		//检查权限
		$this->class = $topic['fid'];
		$this->boardPurview (); //加载权限控制器
		$this->coverPopedom($topic['tclass']);
		$this->checkPopedom('allow_reply');
		
		if($topic['hide']){
			$this->checkPopedom('allow_read_hide');
		}
		if($topic['lock']){
			$this->error('该帖回复已被关闭');
		}
		if($topic['affiche'] && $topic['lock']=='1'){
			$this->error('该帖为公告贴，不可回复');
		}
		if(empty($topic)){
			$this->error('您无法越权访问');
		}
		
		$data ['postInfo'] = $topic;
		
		if ($_GET ['quote']) {
			$data ['quoteInfo'] = $this->post->getPostInfo ( intval ( $_GET ['quote'] ),false );
			if($data['quoteInfo']['tid'] != $tid) {
				$this->error("您无法越权访问");
			}
			$data ['floor'] = $this->post->where ( 'tid=' . $tid . ' AND pid<=' . $data ['quoteInfo'] ['pid'] )->count ();
		}
		if ($_GET ['reppost']) {
			$data ['reppostInfo'] = $this->post->getPostInfo ( intval ( $_GET ['reppost'] ),false );
			if($data['reppostInfo']['tid'] != $tid) {
				$this->error("您无法越权访问");
			}
			$data ['floor'] = $this->post->where ( 'tid=' . $tid . ' AND pid<=' . $data ['reppostInfo'] ['pid'] )->count ();
		}
		
		$postlimit = 20; //多少个回复算一页
		$page = intval ( $_GET ['page'] );
		$this->assign ( $data );
		$this->display ();
	}
	
	function edit() {
		$this->assign("jsType","editer");
		$intId = intval ( $_GET ['pid'] );
		$data ['postInfo'] = $this->post->getPostInfo ( $intId );
		if(empty($data['postInfo'])){
			$this->error ( "帖子不存在" );
		}
		
		if($data['postInfo']['isdel'] == 1){
			$jumpUrl = U('forum/List/index',array("class"=>$data['postInfo']['fid']));
			$this->assign('jumpUrl',$jumpUrl);
			$this->error("帖子不存在");
			exit;
		}
		
		/** 权限控制  **/
		$this->class = $data ['postInfo'] ['fid'];
		$this->specialClassPopedom('allow_edite',"allow_edit_thread",$data,"编辑");
		
		$special = false;
		if($data['postInfo']['istopic']){
			//获取主题信息
			$topicMap['tid'] = $data['postInfo']['tid'];
			$topic = $this->topic->getTopic ( $data['postInfo']['tid'] );
			//检查是否是特殊帖
			$special = $topic['special'] != 0 || in_array(26,explode(',',$topic['sign'])) || in_array(28,explode(',',$topic['sign']));
			$this->assign('hide',$topic['hide']);
			$this->assign('lock',$topic['lock']);
			if($topic['special'] !=0){
				$map['id'] = $topic['special'];
				$template = D('Template')->where($map)->find();
				$map['id'] = array('in',$template['template']);
				$widget = D('ForumTemplateWidget')->where($map)->findAll();
				$newInput = array();
				$widgetData['data'] = unserialize($topic['specialData']);
				$tempWidget = explode(',',$template['template']);
				foreach($tempWidget as $v){
					foreach($widget as $value){
						if($value['id'] == $v){
							$widgetData['widget'] = $value;
							$newInput[] = W("Template".ucfirst($value['widget']),$widgetData,true);
						}
					}
				}
				$this->assign('special',$newInput);
				$this->assign('specialId',$topic['special']);
			}
		}
		
		/** 权限控制 **/
		$data ['tclasslist'] = D ( 'Board' )->gettClass ( $this->class );
		if($data['postInfo']['istopic']){
			$this->getTopNav ( array ("url" => U ( "forum/Detail/index", array ("id" => $data['postInfo']['tid'] ) ), "title" => $data['postInfo']['title'] ) );
		}else{
			$this->getTopNav ( array ("url" => U('forum/Index/gopost',array('id'=>$data['postInfo']['tid'],'pid'=>$data['postInfo']['pid'])), "title" => $data['postInfo']['title'] ) );
		}
		$data['postInfo']['content'] = htmlspecialchars($data['postInfo']['content']);
		$this->assign('isSpecial',$special?1:0);
		//格式化附件数据
		$attachInfo = unserialize($data['postInfo']['attach']);
		$attachInfo = getSubByKey($attachInfo, 'id');
		$data['postInfo']['attach'] = $attachInfo;
		
		$this->assign ( $data );
		$this->display ();
	}
	
	
	function forumlist() {
		$this->class = trim ( $_GET ['class'] );
		$this->boardPurview (); //加载权限控制器
		

		$classInfo = model ( 'Xcate' )->getTree ( 'bbs', $this->class );
		
		$list ['class'] = $this->class;
		$list ['classInfo'] = $classInfo [0];
		foreach ( explode ( ',', $list ['classInfo'] ['m'] ) as $value ) {
			if ($value)
				$manages [] = $value;
		}
		
		$list ['classInfo'] ['m'] = $manages;
		
		$this->getTopNav ( $this->class ); //获取导航
		$category = explode ( ',', $this->class );
		
		$list ['tagid'] = end ( $category );
		
		if (count ( $category ) == 1) {
			
			$child = array ();
			getChildTagId ( array ($list ['classInfo'] ), $child );
			
			$map = "tagid IN (" . implode ( ',', $child ) . ")";
		} else {
			$map = "tagid=" . end ( $category );
		}
		
		$map .= " AND isdel=0";
		
		$list ['isdist'] = intval ( $_GET ['dist'] );
		
		if ($list ['isdist']) {
			$map .= " AND dist=" . $list ['isdist'];
		} else {
			$map .= " AND top=0";
			$list ['tclasslist'] = D ( 'Board' )->gettClass ( $this->class );
		}
		//版块下专题分类
		$list ['tclass'] = intval ( $_GET ['tclass'] );
		if ($list ['tclass'])
			$map .= " AND tclass=" . intval ( $_GET ['tclass'] );
			
		//dump($list['top']);
		//echo $map;
		

		//获取贴子列表，依照分类列表
		$strOrder = $this->_getOrder ();
		
		$list ['topic'] = $this->topic->where ( $map )->field ( '*' )->order ( $strOrder )->findpage ( 40 );
		if ($list ['topic'] ['nowPage'] == 1 && $list ['isdist'] == 0) {
			$list ['top'] = $this->topic->where ( 'isdel=0 AND ( top=2 OR (tagid=' . end ( $category ) . ' AND top=1) )' )->order ( 'top DESC' )->findall ();
		}
		
		$list ['totalCount'] = D ( 'Board' )->getTotalCount ();
		$list ['todayCount'] = D ( 'Board' )->getTodayCount ();
		$this->assign ( $list );
		
		$this->site ['title'] = $list ['classInfo'] ['t'] . ' - ' . L ( 'base_forum' );
		$this->setTitle ();
		$this->display ();
	}
	
	public function selectCategory() {
		$cate = $this->cateService->_format ( $this->_Core ['category'] );
		$json = json_encode ( $cate );
		$this->assign ( 'class', $_GET ['class'] );
		$this->assign ( 'json', $json );
		$this->display ();
	}
	
	function getCategory() {
		$list = D ( 'Board' )->gettClass ( intval ( $_REQUEST ['fid'] ) );
		exit ( json_encode ( $list ) );
	}
	
	//digg
	public function userDig() {
		$options = array ('id' => $_POST ['blogId'], 'uid' => $this->mid, 'type' => 'bbs', "key" => "dig", 'lefttime' => 86400 );
		$uid = $_POST ['fromId'];
		$digResult = X ( 'Browse', $options );
		$digResult->setUid ( $uid );
		$digResult = $digResult->run ();
		switch ($digResult) {
			case - 1 :
				echo "自己不能顶自己";
				break;
			case 1 :
				//dig成功
				$this->topic->setInc ( 'dig', 'id=' . intval ( $_POST ['blogId'] ) );
				echo 1;
				break;
			case 0 :
				//已经dig过了
				echo L ( 'have_digged' );
				break;
			default :
				//超时，将数据统计写入数据库
				$this->topic->setInc ( 'dig', 'id=' . intval ( $_POST ['blogId'] ) );
				echo 1;
				break;
		}
	}
	
	/**
	 * 论坛的RSS输出
	 * depend: formatArray() / getChildTagId()
	 */
	public function generateRss() {
		$item = array ();
		$items = array ();
		$channel = array ();
		
		//1, 获取数据
		$map = array ();
		$limit = 20;
		$order = 'cTime DESC';
		
		//板块中的帖子【仅支持单个板块】
		if (! empty ( $_GET ['cid'] )) {
			$class = trim ( $_GET ['cid'] );
			$classInfo = model ( 'Xcate' )->getTree ( 'bbs', $class );
			$list ['class'] = $class;
			$list ['classInfo'] = $classInfo [0];
			foreach ( explode ( ',', $list ['classInfo'] ['m'] ) as $value ) {
				if ($value)
					$manages [] = $value;
			}
			
			$list ['classInfo'] ['m'] = $manages;
			
			$category = explode ( ',', $class );
			
			$list ['tagid'] = end ( $category );
			
			if (count ( $category ) == 1) {
				
				$child = array ();
				getChildTagId ( array ($list ['classInfo'] ), $child );
				
				$map = "tagid IN (" . implode ( ',', $child ) . ")";
			} else {
				$map = "tagid=" . end ( $category );
			}
			
			$map .= " AND isdel=0";
			
			$list ['isdist'] = intval ( $_GET ['dist'] );
			
			if ($list ['isdist']) {
				$map .= " AND dist=" . $list ['isdist'];
			} else {
				$map .= " AND top=0";
				$list ['tclasslist'] = D ( 'Board' )->gettClass ( $class );
			}
			//版块下专题分类
			$list ['tclass'] = intval ( $_GET ['tclass'] );
			if ($list ['tclass'])
				$map .= " AND tclass=" . intval ( $_GET ['tclass'] );
		
		} else {
			
			$map ['isdel'] = 0;
		
		}
		
		$field = 'id,uid,title,cTime';
		$list = M ( 'bbs_topic' )->where ( $map )->field ( $field )->order ( $order )->limit ( $limit )->findAll ();
		
		//帖子的内容
		$content = array ();
		$ids = formatArray ( $list, 'id' );
		unset ( $map );
		$map ['tid'] = array ('in', $ids );
		$map ['istopic'] = '1';
		$res = M ( 'bbs_post' )->where ( $map )->field ( 'tid,content' )->limit ( $limit )->findAll ();
		foreach ( $res as $v ) {
			$content [$v ['tid']] = $v ['content'];
		}
		
		//作者姓名
		$uids = formatArray ( $list, 'uid' );
		unset ( $map );
		$map ['uid'] = array ('in', $uids );
		$res = M ( 'user' )->where ( $map )->field ( 'uid,fullname' )->limit ( count ( $uids ) )->findAll ();
		foreach ( $res as $v ) {
			$author [$v ['uid']] = $v ['fullname'];
		}
		
		//2, 组装items
		foreach ( $list as $k => $v ) {
			$item = array ('title' => $v ['title'], 'link' => U ( 'bbs/Index/detail', array ('id' => $v ['id'] ) ), 'description' => getShort ( t ( $content [$v ['id']] ), 200 ), //输出200字的摘要
'author' => $author [$v ['uid']], 'guid' => 'bbs_' . $v ['id'], 'pubDate' => $v ['cTime'] );
			
			$items [] = $item;
		}
		
		//3, 组装channel
		$channel = array ('title' => L ( 'rss_title' ), 'link' => U ( 'bbs/Index/index' ), 'description' => L ( 'rss_description' ), //'category'      => '分类1/分类2', //TODO: 分情况
'copyright' => '版权所有，违法必究', 'language' => 'zh-cn', 'lastBuildDate' => time (), 'pubDate' => time (), 'image' => SITE_URL . '/public/themes/blue/images/3MS_logo.gif', 'webMaster' => 'desheng.young@gmail.com', 'items' => $items );
		
		//4, 输出
		$rss = service ( 'RssGenerate' )->generateRssXml ( $channel );
		header ( 'Content-Type:text/xml;charset=utf-8' );
		echo $rss;
	}
	
	public function  paper(){
		$this->display();
	}
	public function video(){
		$this->display();
	}
	public function bbs(){
		$this->display();
	}
}
?>