<?php
/**
 * 论坛列表模型
 */
class ListAction extends CoreAction {
	
	private static $limit = 20;			//结果显示个数
	private static $data = array();		//结果数据
	private static $list = array();		//列表数据
	private static $cache;
	
	//获取帖子列表页面
	public function index() {
		$this->initAndCheck();			//初始并检查权限
		$this->checkForum();			//获取路径
		
		//获取管理员
		self::$list ['admin'] = array_filter(explode(',', $this->_Core['setting']['manager']));
		
		//对列表排序
		$order = isset($_GET['order']) ? "topic.".t($_GET['order'])." DESC" : "topic.rTime DESC";
		$tempS = array('cTime', 'rTime', 'viewcount', 'replycount');
		if(isset($_GET['order']) && !in_array($_GET['order'], $tempS))
			$this->error("参数有误");
		
		$common = "topic.fid=".$this->class." AND topic.isdel=0";
		
		$showHide = 1;
		if(!$this->popedom->check("allow_read_hide")) {
			$common .= " and hide = 0";
			$showHide = 0;
		}
		
		$this->getTopNav();		//获取版块面包屑
		$check = !isset($_GET['cate']) && !isset($_GET['sign']) && !isset($_GET['special']) && !isset($_GET['order']) && intval($_GET['p']) <=3;
		//初始化排序和公用查询条件
		$this->getListData($order, $common);
		//进行帖子处理
		$this->parseTopic();		
		
		//获得全局置顶的帖子
		$top = $this->getTopTopic($order);
		
		//版块分类
		self::$data['tclasslist'] = D('Board')->gettClass($this->class);
		self::$data['top'] = $top;
		
		$this->assign(self::$data);
		
		//获取特殊发帖
		$template = F('forum_template_all');
		if($template){
			$template = D('Template')->findAll();
			F('forum_template_all', $template);
		}
		//存储cookie
		$_SESSION['lastFid'] = $this->class;
		
		$setting = $this->_Core['setting'];
		$this->assign('setting', $setting);
		$this->assign('template', $template);
		$this->site['title'] = self::$list['classInfo']['t'].' - '.L('base_forum');
		$this->setTitle();
		$this->display();
	}
	
	//对帖子的数据进行处理
	private function parseTopic($value) {
		self::$data ['data'] = parseTopic(self::$data ['data'], false);
		
		foreach ( self::$data ['data'] as &$value ) {
			$count = $value ['viewcount'];
			$value ['viewcount'] = $count;
		}
	}

	//获取
	private function getTopTopic($order) {
		$check = $this->popedom->check ( "allow_read_hide" );
		if (!$check) {
			$topWhere = "topic.isdel = 0 and topic.top = 2" . " and topic.hide = 0";
		} else {
			$topWhere = "topic.isdel = 0 and topic.top = 2";
		}
		$top =  $this->__getTopData($order,$topWhere);
		return $top;
	}
	
	private function __getTopData($order,$where){
		$top = M()->Table(C('DB_PREFIX')."forum_topic AS topic LEFT JOIN ".C('DB_PREFIX')."forum_template_type AS template ON topic.special = template.id" )
				  ->field ( 'topic.*' )
				  ->where ( $where )
				  ->order ( $order )
				  ->findAll ();
		$top = parseTopic($top, false);
		foreach($top as &$value) {
			$count = $value ['viewcount'];
			$value ['viewcount'] = $count;
		}
		
		return $top;
	}

	private function getListData($order, $common) {
		switch ($_GET ['special']) {
			case "special" : //特殊贴
				self::$data = M ()->table ( C ( 'DB_PREFIX' ) . "forum_topic as topic 
						left join " . C ( 'DB_PREFIX' ) . "forum_template_type as template
						on topic.special = template.id" )->where ( $common . " AND topic.special=" . intval ( $_GET ['template'] ) )->order ( $order )->findPage ( self::$limit );
				break;
			case "sign" : //帖子的特殊标识
				if (isset ( $_GET ['cate'] )) {
					$where = $common . " and sign.status = 1 and topic.top = 0 and sign.signId = " . intval ( $_GET ['sign'] ) . " AND topic.tclass=" . intval ( $_GET ['cate'] );
				} else {
					$where = $common . " and sign.status = 1 and sign.signId = " . intval ( $_GET ['sign'] );
				}
				
				$count = M()->table ( C ( 'DB_PREFIX' ) . "forum_sign_post as sign
						right join " . C ( 'DB_PREFIX' ) . "forum_topic as topic 
						on topic.tid = sign.tid" )->field('count(topic.tid) as count')->where ( $where )->find();
				
				self::$data = M ()->table ( C ( 'DB_PREFIX' ) . "forum_sign_post as sign
						right join " . C ( 'DB_PREFIX' ) . "forum_topic as topic 
						on topic.tid = sign.tid" )->where ( $where )->order ( $order )->findPage ( self::$limit );
				break;
			case "my" :
				$this->myList ( $common, $order, self::$limit, self::$cache );
				break;
			default :
				$common .= " and (top = 1 or top =0)";
				if (isset ( $_GET ['cate'] )) {
					$where = $common . " and topic.tclass = " . intval ( $_GET ['cate'] );
				} else {
					$where = $common;
				}
				$order = "top DESC," . $order;
				self::$data = M ()->table ( C ( 'DB_PREFIX' ) . "forum_topic as topic 
						left join " . C ( 'DB_PREFIX' ) . "forum_template_type as template
						on topic.special = template.id" )->field ( 'topic.*' )->where ( $where )->order ( $order )->findPage ( self::$limit );
		}
	}
	
	//检查版块是否存在
	private function checkForum() {
		self::$list['path'] = $this->cateService->getCategoryPath($this->class);
		if(!self::$list['path']['path']) {
			$this->error("该板块不存在");
		}
	}
	
	//初始化与检查权限	
	private function initAndCheck() {
		self::$limit = 20;
		$this->class = intval(trim($_GET['class']));
		$this->boardPurview();						//加载权限控制器
		$this->coverPopedom($_GET['cate']);			//覆盖页面上的用户行为权限检测
	}
	
	//获取我的列表数据
	private function myList($common, $order, $limit) {
		switch ($_GET ['type']) {
			case "topic":
				$common .= " AND topic.uid = ".intval($this->mid);
				if(isset($_GET['cate'])) {
					$where = $common." AND topic.tclass = ".intval($_GET['cate']);
				} else {
					$where = $common;
				}
				$data = M()->Table(C('DB_PREFIX')."forum_topic AS topic LEFT JOIN ".C('DB_PREFIX')."forum_template_type AS template ON topic.special = template.id" )
						   ->field('topic.*')
						   ->where($where)
						   ->order($order)
						   ->findPage($limit);
				break;
			case "reply":
				$common .= " AND post.istopic = 0 AND post.uid = ".intval($this->mid);
				if(isset($_GET['cate'])) {
					$where = $common." AND topic.tclass = ".intval($_GET['cate']);
				} else {
					$where = $common;
				}
				$count = M()->Table(C('DB_PREFIX')."forum_topic AS topic LEFT JOIN ".C('DB_PREFIX')."forum_post AS post ON topic.tid = post.tid" )
							->where($where)
							->field("count(1)")
							->order($order)
							->group("topic.tid")
							->findAll();
				$count = count($count);
				$data = M()->Table(C('DB_PREFIX')."forum_topic AS topic LEFT JOIN ".C('DB_PREFIX')."forum_post AS post ON topic.tid = post.tid" )
						   ->field('topic.tid,topic.fid,topic.title,topic.cTime,topic.mTime,topic.rTime,topic.tid,topic.uid,topic.maskId,topic.viewcount,topic.replycount,topic.top,topic.sign,topic.special,topic.highlight,topic.tclass,post.pid')
						   ->where($where)
						   ->order($order)
						   ->group("topic.tid")
						   ->findPage($limit,$count);
				break;
		}
		$this->disposeTopic($data);
		$this->display('my');
		exit();
	}
	
	//进行帖子处理
	private function disposeTopic($data) {
		$data['data'] = parseTopic($data['data'], false);
		
		foreach ( $data ['data'] as &$value ) {
			$count = $value ['viewcount'];
			$value ['viewcount'] = $count;
		}
		
		//版块分类
		$data ['tclasslist'] = D('Board')->gettClass($this->class);
		
		$this->assign($data);
		
		//获取特殊发帖
		$template = D('Template' )->findAll();
		$this->assign('template', $template);
		$this->site['title'] = L('base_forum');
		$this->setTitle();
	}
}
?>