<?php
class DetailAction extends CoreAction {

	private $postlimit = 20; //多少个回复算一页
	protected $TAG = "detail_base";
	private static $rule = "allow";
	private static $forum = array();

	//帖子详细页面
	public function index() {
		$id = intval($_GET['id']);
		self::$forum ['topic'] = $this->topic->getTopic($id);
		//帖子模板以及特殊帖的渲染数据
		self::$forum ['topic'] ['special'] > 2 && $this->topicSpecialTemplate ();
		//帖子数据处理
		$this->parseTopic();

		$this->getForumOtherInfo();			//追加版块的其他信息
		$this->initDataAndCheck();			//初始化帖子数据以及检查
		$this->checkRule();					//权限判断
		$this->borserCount();				//浏览计数
		$this->getTopNav();					//面包线处理
		$this->getTopicReply();				//查看主题的回复信息
		$this->parseTopicContent();			//对主题的内容进行渲染
		$this->getUserCredit();				//获得所有参与用户的积分和精华帖

		$data = $this->getAdminBox();		//管理控件

		//添加
		$count = $this->mask?1:0;
		$this->assign('count1',$count);

		$setting =	$this->_Core['setting'];

		$this->assign('setting',$setting);
		$this->assign($data);
		//添加PID TODO
		self::$forum['topic']['pid'] = $this->post->where('tid='.self::$forum['topic']['tid'].' AND istopic=1')->getField('pid');
		$this->assign(self::$forum );
		$this->site['title'] = self::$forum ['topic'] ['title'] . ' - ' . L ( 'base_forum' );
		$this->setTitle();
		$this->assign('fid',$this->class);
		$this->display("detail");
	}

	private function getAdminBox() {
		$adminService = service("Admin", array($this->popedom));
		$adminCheckbox = $adminService->getAllAdmin ();
		$adminCheckbox = $this->checkSign ( $adminCheckbox, self::$forum ['topic'], false );
		$adminHide = $adminService->getCheckBox ();
		$adminHide = $this->checkSign ( $adminHide, self::$forum ['topic'] );

		//标识管理处理
		$this->getHotSum ( $adminHide );
		$data = array ();
		$data ['adminHide'] = $adminHide;
		$data ['adminHideSign'] = implode ( ',', array_flip ( array_keys ( $adminHide ) ) );

		$data ['adminCheckbox'] = $adminCheckbox;
		$data ['adminCheckboxSign'] = implode ( ',', array_flip ( array_keys ( $adminCheckbox ) ) );
		return $data;
	}

	//获取用户积分与精华数目
	private function getUserCredit() {
		$tempMap = array();
		$user = getSubByKey(self::$forum['list']['data'], 'maskId');
		$user[] = self::$forum['topic']['maskId'];
		$user = array_filter(array_unique( $user ));
		$this->_getUserData($user, "score");

		//获取精华
		$user = array();
		$user = getSubByKey(self::$forum['list']['data'], 'maskId');
		$user[] = self::$forum['topic']['maskId'];
		$user = array_filter(array_unique($user));
		$this->_getUserData($user, "dist");
	}

	//取得用户数据积分或精华数目
	private function _getUserData($user,$type) {
		$tempMap = array ();
		$noDist = array();

		switch($type){
			case "dist":
				$getMethod = "getUserDist";
				$setMethod = "setUserDist";
				$key = "maskId";
				$resultKey = "userDist";
				break;
			case "score":
				$getMethod = "getUserScore";
				$setMethod = "setUserScore";
				$key = "maskId";
				$resultKey = "credit";
				break;
			default:
				return false;
		}

		$temp = $this->_getUserDataSql($user, $key, $type);
		self::$forum[$resultKey] = group($temp, $key);
	}

	//获取用户积分与精华帖的操作方法
	private function _getUserDataSql($user,$key,$type){
		switch($type){
			case "dist":
				$tempMap[$key] = array ('IN', $user);
				$tempMap['dist'] = 1;
				return M('forum_topic')->field('count(1) AS dists, uid, maskName, maskId')->where($tempMap)->group('maskId')->findAll();;
				break;
			case "score":
				$tempMap['uid'] = array('IN', $user);
				return M('credit_user')->field('score AS credit_score, uid AS maskId')->where($tempMap)->findAll();
				break;
			default:
				return false;
		}
	}

	private function parseTopicContent() {
		//处理该帖的内容
		$pageAttach = array ();
		foreach (self::$forum ['list']['data'] as $value){
			if(!empty($value['attach'])){
				foreach($value['attach'] as $v){
					$pageAttach[] = $v;
				}
			}
		}
		self::$forum ['currentUrl'] = U ( 'forum/Index/detail', array ('id' => self::$forum ['topic'] ['tid'] ) );
		$pageAttach = implode(',',getSubByKey($pageAttach,'id'));

		if (! empty ( $pageAttach )) {
			$this->assign ( 'pageAttach', $pageAttach );
		}
	}

	//获取主题的回复
	private function getTopicReply() {
		$map = array();
		$maskMap = array();
		$mask = array();
		$map['tid'] = self::$forum ['topic']['tid'];
		$map['fid'] = self::$forum ['topic']['fid'];

		if($_GET ['auther'] == 1) {
			$maskMap['pid'] = intval($_GET ['pid']);
			$mask = $this->post->where ( $maskMap )->field ( 'maskId,uid' )->find ();
			$map ['maskId'] = intval ( $mask ['maskId'] );
			$map ['uid'] = intval ( $mask ['uid'] );
		}
		self::$forum ['isReadAuther'] = intval ( $_GET ['auther'] );
		self::$forum ['list'] = $this->post->where ( $map )->order ( 'pid ASC' )->findPage ( self::$forum ['limit'] );
		$this->filterWordContent();

		if(empty(self::$forum['list']['data'])) {
			$this->error("帖子不存在");
		}
	}

	private function filterWordContent(){
		$filter = D ( 'FilterWord' );
		foreach ( self::$forum ['list'] ['data'] as $key => $value ) {
			self::$forum ['list'] ['data'] [$key] ['attach'] = unserialize ( self::$forum ['list'] ['data'] [$key] ['attach'] );
			if (! empty ( self::$forum ['list'] ['data'] [$key] ['attach'] )) {
				foreach ( self::$forum ['list'] ['data'] [$key] ['attach'] as $value ) {
					$pageAttach [] = $value ['id'];
				}
			}
			self::$forum ['list'] ['data'] [$key] ['content'] = $filter->filter ( self::$forum ['list'] ['data'] [$key] ['content'] );
		}
	}

	private function parseTopic() {
		self::$forum ['topic'] = array_pop ( parseTopic ( array (self::$forum ['topic'] ), false ) );
	}

	private function borserCount() {
		//写入数据库
		$cache = service ( 'Cache' );
		$count = $cache->getTopicBorser ( self::$forum ['topic'] ['tid'] );
		if ($count === false) {
			$count = self::$forum ['topic'] ['viewcount'];
			$cache->setTopicBorser ( self::$forum ['topic'] ['tid'], $count );
		} else {
			$cache->setTopicBorser ( self::$forum ['topic'] ['tid'] );
			self::$forum['topic']['viewcount'] = $count;
		}
	}
	private function topicSpecialTemplate() {
		$specialMap = array ();
		$tempSpecial = array ();
		$specialType = array ();
		$templateDbData = array ();
		$temp = "";
		$tempType['id'] = self::$forum['topic']['special'];
		$type = M('forum_template_type')->where($tempType)->find();
		self::$forum ['topic'] ['template'] = $type['template'];
		$specialMap ['id'] = array ('in', self::$forum ['topic'] ['template'] );
		$specialInfo = D ( 'ForumTemplateWidget' )->where ( $specialMap )->findAll ();
		foreach ( $specialInfo as $value ) {
			$tempSpecial [$value ['field']] = $value ['label'];
			$specialType [$value ['field']] = $value ['widget'];
		}
		$templateDbData = unserialize ( self::$forum ['topic'] ['specialData'] );
		$templateData = array ();
		foreach ( $templateDbData as $key => $value ) {
			switch ($specialType [$key]) {
				case "url" :
					$temp = str_replace ( "http://", "", $value );
					$templateData [$tempSpecial [$key]] = sprintf ( "<a href='http://%s'>%s</a>", $temp, $value );
					break;
				case "email" :
					$templateData [$tempSpecial [$key]] = sprintf ( "<a href='mailto://%s'>%s</a>", $value, $value );
					break;
				default :
					$templateData [$tempSpecial [$key]] = $value;
			}
		}
		self::$forum ['topic'] ['template'] = $templateData;
	}

	//初始化帖子数据以及检查
	private function initDataAndCheck() {
//		$userSetting = D ( 'Space', 'home' )->getUserSetting ( $this->mid );
//		$this->assign ( 'userSetting', $userSetting );
		self::$forum['topicUrl'] = U('forum/Detail/index', "id=".intval($_GET['id']));
		self::$forum['limit'] = $userSetting ['postNum'] ? $userSetting ['postNum'] : $this->postlimit;
	}

	public function checkSign($data, $topic, $where = "box") {
		$result = $data;
		foreach ( $result as $key => &$value ) {
			$value ['check'] = 0;
			switch ($key) {
				case "allow_commend" :
					foreach ( $topic ['sign'] as $v ) {
						if ($v ['signid'] == 23)
							$value ['check'] = 1;
					}
					break;
				case "allow_top5" :
					$value ['check'] = $topic ['topX'] ? 1 : 0;
					break;
				case "allow_close" :
					$value ['check'] = $topic ['lock'] ? 1 : 0;
					break;
				case "allow_tip" :
					$value ['check'] = $topic ['top'] == 1 ? 1 : 0;
					break;
				case "allow_all_tip" :
					$value ['check'] = $topic ['top'] == 2 ? 1 : 0;
					break;
				case "allow_elite" :
					$value ['check'] = $topic ['dist'] ? 1 : 0;
					break;
				case "allow_hide" :
					$value ['check'] = $topic ['hide'] ? 1 : 0;
					break;
				case "allow_banzhu" :
					$value ['check'] = $topic ['banzhu'] ? 1 : 0;
					break;
			}
		}
		return $result;
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
	 * 火贴处理
	 */
	private function getHotSum($adminHide) {
		$signFindMap ['tid'] = self::$forum ['topic'] ['tid'];
		$signFindMap ['signId'] = array ('in', array (24, 25, 27 ) );
		$findHot = D ( 'ForumSignPost' )->where ( $signFindMap )->findAll ();
		$hasSign = array ();
		$setSign = true;
		if ($findHot) {
			foreach ( $findHot as $value ) {
				$hasSign [] = $value ['signId'];
			}

			if ($this->_Core ['isAdmin'] || $this->_Core ['isWebAdmin']) {
				foreach ( $findHot as $value ) {
					switch ($value ['signId']) {
						case 24 : //火贴
							$adminHide ["allow_hot"] = array ("name" => "火贴标识", "alert" => true, "popup" => false, "url" => "hot", "check" => $value ['status'] );
							break;
						case 25 : //图片帖
							$adminHide ["allow_image"] = array ("name" => "图片标识", "alert" => true, "popup" => false, "url" => "image", "check" => $value ['status'] );
							break;
						case 27 : //附件帖
							$adminHide ["allow_attached"] = array ("name" => "附件标识", "alert" => true, "popup" => false, "url" => "attached", "check" => $value ['status'] );
							break;
					}
				}
				foreach ( $findHot as $value ) {
					$hasSign [] = $value ['signId'];
				}
			}
			if (! in_array ( 24, $hasSign )) {
				$setSign = true;
			} else {
				$setSign = false;
			}
		}



		if ($setSign) {
			$y = self::$forum ['topic'] ['viewcount'];
			$x = self::$forum ['topic'] ['replycount'];
			$hotSum = ($x * $x + ((9 * $x) / 10) + $y / 10) >= 2000;
			if ($hotSum) {
				$this->_setSign ( 24, 1, self::$forum ['topic'] );
			}
		}
	}
	private function _setSign($signId, $Sign, $topic) {
		//处理标识符
		$sign = D ( 'ForumSignPost' );
		$signMap ['tid'] = $topic ['tid'];
		$signMap ['signId'] = $signId;
		//检查是否已经有过这些个标识
		$checkSign = $sign->where ( $signMap )->count ();
		if ($checkSign != 1) {
			$signMap ['cTime'] = time ();
			$signMap ['uid'] = $this->mid;
			$signMap ['status'] = $Sign;
			$sign->add ( $signMap );
		} else {
			$signSave ['status'] = $Sign;
			$signSave ['cTime'] = time ();
			$signSave ['uid'] = $this->mid;
			$sign->where ( $signMap )->save ( $signSave );
		}

		$tid = $topic ['tid'];
		$map ['tid'] = $tempMap ['tid'] = $tid;
		$tempMap ['status'] = 1;
		$sign = D ( 'ForumSignPost' );
		$signData = $sign->where ( $tempMap )->field ( 'tid,signId' )->findAll ();
		if (! empty ( $signData )) {
			$topicSignId = group ( $signData, 'tid' );
			foreach ( $topicSignId as $key => $value ) {
				$saveSignId = getSubByKey ( $value, 'signId' );
				$map ['tid'] = $key;
				$save ['sign'] = implode ( ',', $saveSignId );
				$this->topic->where ( $map )->save ( $save );
			}
		} else {
			$save ['sign'] = "";
			$this->topic->where ( $map )->save ( $save );
		}
		return true;
	}

	//追加版块的其他信息
	private function getForumOtherInfo() {
		self::$forum ['topic'] ['uid'] = self::$forum ['topic']['topicUid'];
		self::$forum ['topic'] ['maskId'] = self::$forum ['topic'] ['topicMaskId'];

		//TODO 要优化：检查是否是投票
		$voteMap ['fieldId'] = self::$forum ['topic'] ['tid'];

		//该帖的积分
		$scoreMap ['pid'] = self::$forum ['topic'] ['tid'];
		$scoreMap ['type'] = "score";
		$score = D ( 'UserCredit' )->where ( $scoreMap )->findAll ();

		self::$forum ['score'] = $score;
		foreach(self::$forum ['topic']['sign'] as $value){
			self::$forum ['isVote'] = 0;
			if($value['signid'] == 26) {
				self::$forum ['isVote'] = 100;
			}
		}
		return self::$forum;
	}

	/**
	 * 权限判断
	 * @param forum
	 */
	private function checkRule() {
		$this->class = self::$forum ['topic'] ['fid'];
		$this->boardPurview ();
		$this->coverPopedom ( self::$forum ['topic'] ['tclass'] );
		$this->checkPopedom ( "allow_read" );
		if (self::$forum ['topic'] ['hide'] != 0) {
			$this->checkPopedom ( "allow_read_hide" );
		}

		if ($this->_Core ['isAdmin'] || $this->_Core ['isWebAdmin']) {
			$this->assign ( 'jsType', "detail_admin" );
		}

		$lastFid = $_SESSION['lastFid'];
		$check = (self::$forum ['topic'] ['isdel'] || ! self::$forum ['topic']) && !(!empty($_GET['admincheck'])&& model('UserGroup')->isAdmin($this->mid));
		if($check){
			if(!self::$forum['topic']){
				$jumpUrl = U('index/List/index',array("class"=>self::$forum['topic']['fid']));
			}elseif(!empty($lastFid)){
				$jumpUrl = U('index/List/index',array("class"=>$lastFid));
			}else{
				$jumpUrl = U('index/Index/index');
			}
			$this->assign('jumpUrl',$jumpUrl);
			$this->assign('status',1);
			$this->error ( "帖子不存在" );
		}
	}
}
?>