<?php
/**
 * 论坛操作模型
 */
class DoAction extends CoreAction {
	
	var $credit;
	private static $hasImage = false;			//是否存在图片
	
	public function _initialize() {
		parent::_initialize ();
		$this->credit = service('Credit');
	}
	
	public function subscibeCate() {
		$content = json_decode ( $_POST ['subId'] );
		echo service ( 'Follow' )->addFollowData ( $content, 'bbs' );
	}
	
	public function deleteSubscibeCate() {
		$cid = h ( $_POST ['subId'] );
		echo service ( 'Follow' )->delFollowData ( $cid, 'bbs' );
	}
	
	//添加主题操作
	public function post() {
		$time = time();
		
		/** 附件检测 **/
		$hide = intval($_POST['hide']);
		$close = intval($_POST['close']);
		$affiche = intval($_POST['affiche']);
		$special = intval($_POST['specialId']);
		
		//检查权限
		$this->addNewCheckPopedom($hide, $close, $affiche);
		
		if($special != 0) {
			$specialInfo = D('Template')->where('id='.$special)->find();
		}
		
		//输入判断
		if(isset($_POST['template']) && !empty($_POST['template'])) {
			$specialData = $this->checkTemplateMustInput();
		}
		
		$list = $this->getAttach();
		
		//验证表单数据的正确性
		$_POST['title'] = htmlspecialchars($_POST['title'], ENT_QUOTES);
		if(isset($_POST['content'])) {
			$_POST['content'] = str_replace("<p>", '', $_POST['content']);
			$_POST['content'] = str_replace("</p>", '<br>', $_POST['content']);
			$testtest = trim($this->_deleteBr(t($_POST['content'])));
			if(empty($testtest) && $testtest !== 0) {
				$this->error("内容不能为空");
			}
		}
		if(isset($_POST['title'])) {
			$testtest = t($_POST['title']);
			if(empty($testtest) && $testtest != 0) {
				$this->error ( "标题不能为空" );
			} else if (get_str_length($testtest) > 100) {
				$this->error('标题不能超过100个字');
			}
		}
		
		$content = h($_POST['content']);
		if(empty($content) && $content != 0) {
			$this->error ( "帖子内容不能为空" );
		} else if (get_str_length($content) > 5000) {
			$this->error('帖子内容不能超过5000个字');
		}
		

		if($this->topic->create()) {
			//添加附件
			if($_POST['attach']) { 
				$this->topic->attach = 1;
//				$this->credit->set ( $this->mid, 'upload' );
			}
			
			$this->topic->fid = $this->class;
			$this->topic->uid = $this->mid;
			$this->topic->cTime = $time;
			$this->topic->special = $special;
			$maskName = $this->setMaskInfo('topic');
			$this->topic->maskId = $maskName['uid'];
			$this->topic->maskName = $maskName['uname'];
			$this->topic->rTime = $time;
			$this->topic->tclass - intval($_POST['tclass']);
			$this->topic->lastreuid = $this->mid;
			$this->topic->lastMaskName = $maskName['uname'];

			//检查特殊标识的逻辑
			if ($hide != 0) {
				$this->topic->hide = 1;
				$sign [] = 4;
			}
			if ($close != 0) {
				$this->topic->lock = 1;
			}
			if ($affiche != 0) {
				$this->topic->affiche = 1;
				$sign [] = 28;
			}
			if (self::$hasImage) {
				$sign [] = 25;
			}
			if (! self::$hasImage && ! empty ( $list )) {
				$sign [] = 27;
			}
			if ($special != 0) {
				$sign [] = $specialInfo ['sign'];
			}
			if (! empty ( $sign )) {
				$this->topic->sign = implode ( ',', $sign );
			}
			
			if($tid = $this->topic->add()) {
				foreach ( $list as $k => $v ) {
					$map ['tid'] = $tid;
					$map ['attach_id'] = $v ['id'];
					$re = M ( 'forum_attach' )->add ( $map );
				}
				
				//将Hide写入数据库
				if ($hide != 0) {
					$signMap ['signId'] = 4;
					$signMap ['tid'] = $tid;
					D ( 'ForumSignPost' )->add ( $signMap );
				}
				
				if ($affiche != 0) {
					$signMap ['signId'] = 28;
					$signMap ['tid'] = $tid;
					D ( 'ForumSignPost' )->add ( $signMap );
					$AfficheMap ['tid'] = $tid;
					D ( 'ForumAffiche' )->add ( $AfficheMap );
				}
				
				if (self::$hasImage) {
					$signMap ['signId'] = 25;
					$signMap ['tid'] = $tid;
					D ( 'ForumSignPost' )->add ( $signMap );
				}
				
				if ($special != 0) {
					//特殊贴
					if ($specialInfo ['sign'] != 0) {
						$signMap ['signId'] = $specialInfo ['sign'];
						$signMap ['tid'] = $tid;
						D ( 'ForumSignPost' )->add ( $signMap );
					}
					
					$specialMap ['tid'] = $tid;
					$specialMap ['special'] = $special;
					$specialMap ['specialData'] = serialize ( $specialData );
					D ( 'ForumSpecialTopic' )->add ( $specialMap );
				}
				
				if (! self::$hasImage && ! empty ( $list )) {
					$signMap ['signId'] = 27;
					$signMap ['tid'] = $tid;
					D ( 'ForumSignPost' )->add ( $signMap );
				}
				
				$this->post->create();
				$this->post->uid = $this->mid;
				$this->post->tid = $tid;
				$this->post->fid = $this->class;
				$this->post->title = t ( $_POST ['title'] );
				$content = h($this->_deleteBr($content),'base');
				$this->post->content = $content;
				$this->post->istopic = 1;
				$postMaskName = $this->setMaskInfo();
				$this->post->maskId = $postMaskName['uid'];
				$this->post->maskName = $postMaskName['uname'];
				$this->post->cTime = $time;
				$this->post->attach = serialize ( $this->__parseAttach ( $_POST ['attach'] ) );
				$result = $this->post->add();
				
				$sql = "UPDATE `".C('DB_PREFIX')."forum` SET `lastpost_uid`={$this->mid},`topic_count`=topic_count+1,`lastpost_time`={$time},`lastpost_post_tid`={$tid} WHERE ( `fid` = '{$this->class}' )";
				M()->execute($sql);
				
				$this->credit->setUserCredit($this->mid, 'forum_post');			//积分
				redirect(U('forum/Detail/index', array('id' => $tid)));
			} else {
				$this->error ( L ( 'do_thread_add_error' ) );
			}
		} else {
			$this->error($this->topic->getError());
		}
	}
	
	//获取附件
	private function getAttach() {
		$list = X('Xattach')->getAttach($_POST['attach']);
		unset($_POST['Xattach']);
		$image = array("jpg", "jpeg", "gif", "bmp","png");
		foreach($list as $key => $value) {
			$temp = in_array(strtolower($value['extension']), $image);
			if($temp)
				self::$hasImage = true;
			$_POST ['attach'] [$key] = $value ['id'] . '|' . $value ['name'];
		}
		return $list;
	}

	//检查模板是否必须输入
	private function checkTemplateMustInput() {
		$specialData = array();
		if(isset($_POST['must'])) {
			foreach($_POST['must'] as $key => $value) {
				if($_POST['template'][$value] !== 0 && empty($_POST['template'][$value])) {
					$this->error($key.L('add_special'));
				}
			}
		}
		$specialData = $_POST['template'];
		return $specialData;
	}

	//新权限的判断
	private function addNewCheckPopedom($hide, $close, $affiche) {
		$this->class = trim ( $_POST ['board'] );
		$this->boardPurview (); //加载权限控制器
		$this->coverPopedom ( intval ( $_POST ['tclass'] ) );
		
		if ($hide != 0) {
			$this->checkPopedom ( "allow_post_hide" );
		}
		if ($close != 0) {
			$this->checkPopedom ( "allow_post_close" );
		}
		
		if ($affiche != 0) {
			$this->checkPopedom ( "allow_exalt" );
		}
	}

	//对帖子进行回复操作
	public function reply() {
		$tid = isset($_POST['tid']) ? intval($_POST['tid']) : 0;
		$topic = $this->topic->getTopic($tid);
		
		//验证帖子是否可以回复
		if(!$topic ['tid']) {
			$this->error(L('do_post_wrong'));
			exit();
		} else if($topic ['lock'] == 1) {
			$this->error(L('do_post_wrong_islock'));
			exit();
		} else if ($topic['isdel'] == 1) {
			$jumpUrl = U('forum/List/index',array("class"=>$topic['fid']));
			$this->assign('jumpUrl',$jumpUrl);
			$this->error("主题不存在");
			exit();
		}
		
		$this->class = $topic['fid'];
		//权限检测
		$this->boardPurview (); //加载权限控制器
		$this->coverPopedom ( $topic ['tclass'] );
		$this->checkPopedom ( "allow_reply" );
		
		if($topic['hide'] == 1) {
			$this->checkPopedom("allow_read_hide");
		}
		
		/** 附件检测 **/
		$list = X('Xattach' )->getAttach($_POST['attach']);
		unset($_POST['Xattach']);
		$image = array("jpg", "jpeg", "gif", "bmp", "png");
		$hasImage = false;
		foreach($list as $key => $value) {
			$temp = in_array(strtolower($value['extension']), $image);
			if($temp) {
				$hasImage = true;
			}
			$_POST['attach'][$key] = $value['id'].'|'.$value['name'];
//			$this->credit->set ( $this->mid, 'upload' );
		}

		//执行回复操作
		if(t($_POST['quickreply']) == 'quickreply'){
			$_POST['content'] = trim($_POST['content']);
			$content  = str_replace("\n","<br>",$_POST['content']);
		}else{
			$content = $_POST ['content'];
		}
		$testtest = trim ( $this->_deleteBr(t ( $content ) ));
		if ($testtest !==0 && empty ( $testtest )) {
			$this->error ( "内容不能为空" );
		}
		if (get_str_length($testtest) > 1000) {
			$this->error('回帖内容不能超过1000个字');
		}

		$this->post->create ();
		$this->post->fid = $this->class;
		$this->post->tid = $topic ['tid'];
		$this->post->uid = $this->mid;
		$this->post->quote = intval ( $_POST ['quote'] );
		$content = h($this->_deleteBr($content),'base');
		$this->post->content = $content;
		$this->post->title = t( $_POST ['title'], ENT_QUOTES );
		$this->post->ip = get_client_ip ();
		$this->post->cTime = time ();
		$this->post->attach = serialize ( $this->__parseAttach ( $_POST ['attach'] ) );
		$postMaskName = $this->setMaskInfo();
		$this->post->maskId = $postMaskName['uid'];
		$this->post->maskName = $postMaskName['uname'];
		$result = $this->post->add();
		if ($result) {
			foreach ( $list as $key => $value ) {
				$map ['tid'] = $topic ['tid'];
				$map ['attach_id'] = $value ['id'];
				$map ['pid'] = $result;
				$r = M ( 'forum_attach' )->add ( $map );
			}
			$this->credit->setUserCredit($this->mid, 'forum_reply');		//积分
			if($hasImage){
				$sign = explode(',',$topic['sign']);
				foreach($sign as $key =>$value){
					if($value == 27) unset($sign[$key]);
				}
				if(!in_array(25,$sign)){
					$sign [] = 25;
					$sign = array_filter($sign);
					$sign = array_unique($sign);
					$data['sign'] = implode(',',$sign);
					
					$temp2['tid'] = $topic['tid'];
					$temp2['signId'] = 27;
					D('ForumSignPost')->where($temp2)->delete();
					
					$signMap ['signId'] = 25;
					$signMap ['tid'] = $topic['tid'];
					D ( 'ForumSignPost' )->add ( $signMap );
					M('forum_topic')->where('tid='.$topic['tid'])->save($data);
				}
			
			}
			
			if (! $hasImage && ! empty ( $list )) {
				$sign = explode(',',$topic['sign']);
				if(!in_array(25,$sign) && !in_array(27,$sign)){
					$sign [] = 27;
					$sign = array_filter($sign);
					$sign = array_unique($sign);
					$data['sign'] = implode(',',$sign);
					
					$temp2['tid'] = $topic['tid'];
					$temp2['signId'] = 25;
					D('ForumSignPost')->where($temp2)->delete();
					
					$signMap ['signId'] = 27;
					$signMap ['tid'] = $topic['tid'];
					D ( 'ForumSignPost' )->add ( $signMap );
					M('forum_topic')->where('tid='.$topic['tid'])->save($data);
				}
			}
			
			$sql = "UPDATE `".C('DB_PREFIX')."forum_topic` SET `rTime`=".time().",`lastreuid`={$this->mid},`lastMaskName`='{$maskName}',replycount=replycount+1 WHERE tid={$topic['tid']}";
			$this->topic->execute($sql);
		}
		//页面跳转
		if(isset($_POST['jump']) && intval($_POST['jump']) == 1) {
			redirect(U('forum/Detail/index', array('id'=>$tid, 'p'=>'last')).'#p'.$result);
		} else {
			redirect(U('forum/Detail/index', array('id'=>$tid)).'#p'.$result);
		}
	}
	
	//去掉文本中的br标签
	private function _deleteBr($content) {
		$content = trim($content);
		while(strpos($content,'&nbsp;') === 0){
			$content = substr($content,6);
		}
		$content = trim($content);
		while(strpos($content,'<br>') === 0){
			$content = substr($content,4);
		}
		return $content;
	}
	
	//执行帖子或回复的编辑操作
	public function doedit() {
		//输入判断
		if(isset($_POST['template']) && !empty($_POST['template'])) {
			$specialId = intval($_POST['specialId']);
			$specialData = array();
			if(isset($_POST['must'])) {
				foreach($_POST['must'] as $key => $value) {
					if($_POST['template'][$value] !== 0 && empty($_POST['template'][$value])) {
						$this->error($key.L('add_special'));
					}
				}
			}
			$filterTemp = array ("script", "iframe", "alert");
			$filter = array();
			foreach($filterTemp as $value) {
				$filter[0][] = $value;
				$filter[1][] = chunk_split($value, 2, "-");
			}
//			foreach ( $_POST ['template'] as &$value ) {
//				$value = str_ireplace ( $filter [0], $filter [1], h ( $value ) );
//			}
//			$specialData = $_POST ['template'];
			foreach($_POST['template'] as $key => $val) {
				$specialData[$key] = str_ireplace($filter[0], $filter[1], h($val));
			}
		}
		$intPid = intval ( $_POST ['pid'] );
		$data ['postInfo'] = $this->post->getPostInfo ( $intPid );
		//判断是否存在
		if($data['postInfo']['isdel'] == 1 || empty($data['postInfo'])){
			$jumpUrl = U('forum/List/index',array("class"=>$data['postInfo']['fid']));
			$this->assign('status',1);
			$this->assign('jumpUrl',$jumpUrl);
			$this->error("帖子不存在");
			exit;
		}
		
		/** 权限控制  **/
		$this->class = $data ['postInfo'] ['fid'];
		$this->specialClassPopedom('allow_edite',"allow_edit_thread",$data,"编辑");

		$hide = intval ( $_POST ['hide'] );
		$close = intval ( $_POST ['close'] );
		
		if ((! $data ['postInfo'] ['istopic'] && $_POST ['content']) || ($data ['postInfo'] ['istopic'])) {
			$testtest = trim ( t ( $_POST ['content'] ) );
			if ($data ['postInfo'] ['istopic']) {
				$topicData = $this->topic->where ( 'tid=' . $data ['postInfo'] ['tid'] )->field ( 'special,sign' )->find ();
				$special = $topicData ['special'] != 0 || in_array ( 26, explode ( ',', $topicData ['sign'] ) );
				if (! $special && empty ( $testtest ))
					$this->error ( "内容不能为空" );
			} else {
				if (empty ( $testtest ))
					$this->error ( "内容不能为空" );
			}
			
			/** 附件检测 **/
			$list = X ( 'Xattach' )->getAttach ( $_POST ['attach'] );
			//编辑时forum_attach进行重新插值
			$p = M('forum_post')->where("pid=$intPid")->find();
			if($p['istopic']==1){
				$map['tid'] = $p['tid'];
				$map['pid'] = 0;
				M('forum_attach')->where($map)->delete();
				M('forum_sign_post')->where("tid=".$p['tid'])->delete();
				M('forum_topic')->where("tid=".$p['tid'])->setField("sign",'');
				foreach ($list as $key=>$value){
					$d['tid'] = $p['tid'];
					$d['pid'] = 0;
					$d['attach_id'] = $value['id'];
					$attachid = M('forum_attach')->add($d);
				}
			}else{
				$map['tid'] = $p['tid'];
				$map['pid'] = $intPid;
				M('forum_attach')->where($map)->delete();
				foreach ($list as $key=>$value){
					$d['tid'] = $p['tid'];
					$d['pid'] = $intPid;
					$d['attach_id'] = $value['id'];
					M('forum_attach')->add($d);
				}
			}
			unset ( $_POST ['Xattach'] );
			$image = array ("jpg", "jpeg", "gif", "bmp","png" );
			$hasImage = false;
			foreach ( $list as $key => $value ) {
				$temp = in_array ( strtolower($value ['extension']), $image );
				if ($temp){
					$hasImage = true;
				}
				$_POST ['attach'] [$key] = $value ['id'] . '|' . $value ['name'];
			}
			//添加....判断此帖子是否还有图片
			if($hasImage == false){
				$attachAll = M('')->table(C('DB_PREFIX')."forum_attach as a left join ".
										  C('DB_PREFIX')."x_attach as b on a.attach_id=b.id")
										->field('b.extension')->where('a.tid='.$p['tid'])->findAll();
				foreach($attachAll as $ke=>$v){
					if(in_array(strtolower($v['extension']),$image)){
						$hasImage = true;
						break;
					}
				}
			}
			if ($data ['postInfo'] ['istopic']) {
				
				if ($hide != 0) {
					$this->checkPopedom ( "allow_post_hide" );
				}
				if ($close != 0) {
					$this->checkPopedom ( "allow_post_close" );
				}
				
				$topicdata ['title'] = htmlspecialchars ( $_POST ['title'], ENT_QUOTES );
				$topicdata ['tclass'] = intval ( $_POST ['tclass'] );
				$topicdata ['attach'] = ($_POST ['attach']) ? 1 : 0;
				$topicdata ['rTime'] = time ();
				$sign = array ();
				$tempSign = explode ( ',', $topicData ['sign'] );
				$tempSginArray = array (4, 3, 25, 27 );
				
				if ($hide != 0) {
					$topicdata ['hide'] = 1;
					$sign [] = 4;
				} else {
					$topicData ['hide'] = 0;
				}
				if ($close != 0) {
					$topicdata ['lock'] = 1;
				} else {
					$topicdata ['lock'] = 0;
				}
				if ($hasImage) {
					$sign [] = 25;
				}
				if (! $hasImage && ! empty ( $list )) {
					$sign [] = 27;
				}
				if(!$hasImage && !empty($attachAll)){
					$sign [] = 27;
				}
				$tempSelectSign = $sign;
				$unsetSign = array_diff ( $tempSginArray, $tempSelectSign );
				$sign = array_merge ( $tempSign, $sign );
				foreach ( $sign as $key => $value ) {
					if (in_array ( $value, $unsetSign )||empty($sign[$key])) {
						unset ( $sign [$key] );
					}
				}
				$sign = array_unique ( $sign );
				if (! empty ( $sign )) {
					$topicdata ['sign'] = implode ( ',', $sign );
				}
				
				$this->topic->where ( 'tid=' . $data ['postInfo'] ['tid'] )->data ( $topicdata )->save ();
				$savedata ['title'] = $topicdata ['title'];
				
				if ($hide != 0) {
					$signMap ['signId'] = 4;
					$signMap ['tid'] = $data ['postInfo'] ['tid'];
					D ( 'ForumSignPost' )->add ( $signMap );
				}
				if ($close != 0) {
					$signMap ['signId'] = 3;
					$signMap ['tid'] = $data ['postInfo'] ['tid'];
					D ( 'ForumSignPost' )->add ( $signMap );
				}
				
				if ($specialId != 0) {
					$specialMap ['special'] = $specialId;
					$specialMap ['tid'] = $data ['postInfo'] ['tid'];
					$specialdata ['specialData'] = serialize ( $specialData );
					D ( 'ForumSpecialTopic' )->where ( $specialMap )->data ( $specialdata )->save ();
				}
				
				if ($hasImage) {
					$signMap ['signId'] = 25;
					$signMap ['tid'] = $data ['postInfo'] ['tid'];
					D ( 'ForumSignPost' )->add ( $signMap );
				}
			}else{
				if (!$hasImage) {
					$ts = explode ( ',', $topicData ['sign'] );
					foreach ($ts as $key=>$value){
						if($value==25){
							unset($ts[$key]);
						}
					}
					$changeSign['sign'] = implode(',',$ts);
					$this->topic->where ( 'tid=' . $data ['postInfo'] ['tid'] )->data ( $changeSign )->save ();
				}
			}
			$savedata ['muid'] = $this->mid;
			$savedata ['mTime'] = time ();
			$savedata ['content'] = h ( $_POST ['content'], 'base' );
			$savedata ['attach'] = serialize ( $this->__parseAttach ( $_POST ['attach'] ) );
			$this->post->where ( 'pid=' . $intPid )->data ( $savedata )->save ();
			$this->credit->setUserCredit($data['postInfo']['uid'], 'forum_edit');
			U ( 'forum/detail/index', array ('id' => $data ['postInfo'] ['tid'] ) ,true ) ;
		}
	
	}
	
	private function __parseAttach($data) {
		$result = array ();
		foreach ( $data as $key => $value ) {
			$temp = explode ( '|', $value );
			$result [$key] ['id'] = $temp [0];
			$result [$key] ['name'] = $temp [1];
		}
		return $result;
	}
	
	function dodel() {
		$intTid = intval ( $_POST ['tid'] );
		$data ['postInfo'] = $this->post->getPostInfo ( $intTid );
		
		$this->specialClassPopedom('allow_delete',"allow_delete_thread",$data,"删除");
		
		if ($data ['postInfo'] ['istopic']) {
			M ( 'bbs_classify' )->where ( "tid=" . $data ['postInfo'] ['tid'] )->delete ();
			$this->topic->where ( "id=" . $data ['postInfo'] ['tid'] )->delete ();
			$this->post->where ( "tid=" . $data ['postInfo'] ['tid'] )->delete ();
			$this->credit->setUserCredit($data['postInfo']['tuid'], 'forum_delete_post');
			echo '1';
		} else {
			$this->post->setField ( 'isdel', 1, "id=" . $intTid );
			$this->credit->setUserCredit($data['postInfo']['uid'], 'forum_delete_reply');
			echo '2';
		}
	}
	
	function doAdminStatus() {
		$intTid = intval ( $_POST ['tid'] );
		$status = intval ( $_POST ['status'] );
		$statusMessage = ($status == 1) ? L ( 'set' ) : L ( 'cancel' );
		
		$topicInfo = $this->topic->getTopic ( $intTid );
		
		/** 权限控制  **/
		$this->class = $_POST ['board'];
		$this->boardPurview ( true ); //载入权限控制器
		/** 权限控制 **/
		
		switch ($_POST ['type']) {
			case 'dist' : //精华
				$message = L ( 'thread_soul' );
				$result = $this->topic->setAdminStatus ( $this->class, $intTid, 'dist', $status );
				if ($result) { //积分
					if ($status == '1') {
						$this->credit->setUserCredit($topicInfo['uid'], 'forum_dist');
					} else if ($status == '0') {
						$this->credit->setUserCredit($topicInfo['uid'], 'forum_undist');
					}
				}
				break;
			
			case 'alltop' :
				$message = L ( 'thread_all_top' );
				if ($this->_Core ['isWebAdmin']) {
					$result = $this->topic->setAdminStatus ( $this->class, $intTid, 'top', $status );
				} else {
					$result = false;
				}
				break;
			
			case 'top' : //置顶
				$message = L ( 'thread_top' );
				$result = $this->topic->setAdminStatus ( $this->class, $intTid, 'top', $status );
				break;
			
			case 'lock' : //锁定
				$message = L ( 'thread_lock' );
				$result = $this->topic->setAdminStatus ( $this->class, $intTid, 'lock', $status );
				break;
		}
		$message = $statusMessage . $message;
		if ($result) {
			$this->success ( $message . L ( 'success' ) );
		} else {
			$this->success ( $message . L ( 'error' ) );
		}
	}
	
	private function actionInit($action, $check = true) {
		if (! is_array ( $_POST ['id'] )) {
			$_POST ['id'] = explode ( ',', $_POST ['id'] );
		}
		$tid = array_filter ( array_map ( "intval", $_POST ['id'] ) );
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		$field = array ("uid", "maskId",'maskName', "tid", "lock", "sign", "tclass", "fid", 'top', 'topX', 'dist', 'hide' ,'banzhu');
		$res = $this->topic->where ( $map )->field ( $field )->findAll ();
		if (C ( 'MEMCACHED_ON' ) && $res[0]['top'] == 2) {
			$cache = service ( 'Cache' );
			$cache->cleanTopForumList();
		}
		
		
		if (C ( 'MEMCACHED_ON' )) {
			$cache = service ( 'Cache' );
			foreach($res as $value){
				$cache->cleanTopicData($value['tid']);
			}
			$cache->cleanForumList($res[0]['fid']);
		}
		foreach($res as $value){
			F('forum_topic_credit'.$value['tid'],null);
		}
		
		//检查帖子
		if (! $res) {
			$this->error ( "帖子不存在" );
		}
		//检查权限
		if ($check) {
			$this->class = $res [0] ['fid'];
			$this->boardPurview ( $action ); //加载权限控制器
		}
		$result ['tid'] = $tid;
		$result ['topic'] = $res;
		return $result;
	}
	
	private function actionPostInit($action, $check = true) {
		$pid = array_filter ( array_map ( "intval", $_POST ['id'] ) );
		$res = $this->post->getPostInfos ( $pid );
		//检查帖子
		if (! $res) {
			$this->error ( "回帖不存在" );
		}
		//检查权限
		if ($check) {
			$this->class = intval ( $res [0] ['fid'] );
			$this->boardPurview ( $action ); //加载权限控制器
		}
		$result ['pid'] = $pid;
		$result ['topic'] = $res;
		return $result;
	}
	
	public function deletePost() {
		$check = $this->actionPostInit ( '', false );
		$topic = $check ['topic'];
		$pid = $check ['pid'];
		
		$this->class = $topic [0] ['fid'];
		$tid         = $topic[0]['tid'];
		$data = array();
		$data['postInfo']['tclass'] = $this->class;
		$data['postInfo']['uid']    = $topic[0]['uid'];
		$this->specialClassPopedom('allow_delete',"allow_delete_thread",$data,"删除");
		
		if (count ( $pid ) < 1) {
			$map ['pid'] = $pid;
		} else {
			$map ['pid'] = array ('in', $pid );
		}
		$save ['isdel'] = 1;
		$result = $this->post->where ( $map )->save ( $save );
		//删除回帖
		$image = array('jpeg','jpg','gif','png','bmp');
	    $res = M('forum_attach')->where($map)->delete();
		$attachAll = M('')->table(C('DB_PREFIX')."forum_attach as a left join ".
								 C('DB_PREFIX')."x_attach as b on a.attach_id=b.id")
								->field('b.extension')->where('a.tid='.$tid)->findAll();
		foreach($attachAll as $ke=>$v){
			if(in_array(strtolower($v['extension']),$image)){
				$hasImage = true;
				break;
			}
		}
		if(!$hasImage){
			$topicSign = $this->topic->where("tid=$tid")->find();
			$sign = explode(',',$topicSign['sign']);
			foreach($sign as $key=>$value){
				if($value == 25||$value==27){
					unset($sign[$key]);
				}
			}
			$signid['sign'] = implode(',',$topicSign['sign']);
			$this->topic->where("tid=$tid")->save($signid);
		}
		if ($result) {
			foreach ( $topic as $value ) {
				$topicMap ['tid'] = $value ['tid'];
				$postMap ['tid'] = $value ['tid'];
				$postMap ['isdel'] = 0;
				$postMap ['istopic'] = 0;
				$data ['replycount'] = $this->post->where ( $postMap )->count ();
				$this->topic->where ( $topicMap )->save ( $data );
			}
			
			D ( 'Log' )->put ( $this->mid, $this->class, "删除了" . count ( $topic ) . "个回复" );
			
			$setting = D ( 'ForumSetting' );
			foreach ( $topic as $value ) {
				$this->credit->setUserCredit($value['uid'], 'forum_delete_reply');
			}
			$this->success ( U ( 'forum/Detail/index', array ("id" => $topic [0] ['tid'] ) ) );
		} else {
			$this->error ( "操作失败" );
		}
	}
	
	private function dump() {
		$check = $this->actionInit ( 'allow_exalt' );
		$tid = $check ['tid'];
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		$save ['rTime'] = time ();
		$res = $this->topic->where ( $map )->save ( $save );
	}
	
	private function _setSign($signId, $Sign, $topic) {
		//处理标识符
		$sign = D ( 'ForumSignPost' );
		foreach ( $topic as $value ) {
			//对于特殊的不能覆盖存在进行额外判定
			$topArray = array (6, 7, 8, 9, 10 );
			$commend = array (1, 2 );
			if (in_array ( $signId, $topArray )) {
				$topKey = array_search ( $signId, $topArray );
				if ($topKey !== FALSE) {
					unset ( $topArray [$topKey] );
				}
				$specialMap ['tid'] = $value ['tid'];
				$specialMap ['signId'] = array ('in', $topArray );
				$specialSave ['cTime'] = time ();
				$specialSave ['uid'] = $this->mid;
				$specialSave ['status'] = 0;
				$sign->where ( $specialMap )->save ( $specialSave );
			}
			if (in_array ( $signId, $commend )) {
				$commendKey = array_search ( $signId, $commend );
				if ($commendKey !== FALSE) {
					unset ( $commend [$commendKey] );
				}
				$specialMap ['tid'] = $value ['tid'];
				$specialMap ['signId'] = array ('in', $commend );
				$specialSave ['cTime'] = time ();
				$specialSave ['uid'] = $this->mid;
				$specialSave ['status'] = 0;
				$sign->where ( $specialMap )->save ( $specialSave );
			}
			
			$signMap ['tid'] = $value ['tid'];
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
		}
		return true;
	}
	
	public function saveModel() {
		$parseTag = service ( 'ParseTag' );
		$widgetTags = $this->_getTagWidget ( $_POST );
		if (is_array ( $widgetTags )) {
			$result ['html'] = $parseTag->parse ( $widgetTags [0], true );
			$result ['widget'] = $widgetTags [1];
		
		} else {
			$result ['html'] = $parseTag->parse ( $widgetTags, true );
			$result ['widget'] = $widgetTags;
		}
		$result ['sign'] = $parseTag->getSign ( $_POST ['tagName'] );
		$widgetMap ['id'] = intval ( $_POST ['widgetId'] );
		$widgetSave ['signId'] = $result ['sign'];
		$widgetSave ['widget'] = $result ['widget'];
		D ( 'ForumWidget' )->where ( $widgetMap )->save ( $widgetSave );
		echo json_encode ( $result );
	}
	
	public function previewModel() {
		$parseTag = service ( 'ParseTag' );
		$widgetTags = $this->_getTagWidget ( $_POST );
		if (is_array ( $widgetTags )) {
			$result ['html'] = $parseTag->parse ( $widgetTags [0], true );
			$result ['widget'] = $widgetTags [1];
		} else {
			$result ['html'] = $parseTag->parse ( $widgetTags, true );
			$result ['widget'] = $widgetTags;
		}
		echo json_encode ( $result );
	}
	
	private function _getTagWidget($post) {
		$tagName = $post ['tagName'];
		unset ( $post ['tagName'] );
		switch ($tagName) {
			case "forum:about" :
				
				$param = $post;
				$safetyArray = array ("title", "tplHeight", "limit", "words" );
				$safetyArray2 = array ("(",")","<",">",'"',"'","]","[",";");
				foreach ( $param as $key => $value ) {
					if (strpos ( $key, 'PARAM_' ) !== false && ! empty ( $value )) {
						$key = str_replace ( 'PARAM_', '', $key );
						$value = str_replace($safetyArray2,"",$value);
						if (in_array ( $key, $safetyArray )){
							$value = t ( $value );
						}
						$attr [] = $key . "=\"" . $value . "\"";
					}
				}
				$value = "";
				
				$filterTemp = array ("script", "iframe", "alert" );
				$filter = array ();
				foreach ( $filterTemp as $value ) {
					$filter [0] [] = $value;
					$filter [1] [] = chunk_split ( $value, 2, "-" );
				}
				if (isset ( $post ['linkData'] ) && ! empty ( $post ['linkData'] )) {
					$value = str_replace ( $filter [0], $filter [1], $post ['linkData'] );
				}
				$attr = implode ( ' ', $attr );
				return sprintf ( '<%s %s>%s</%s>', $tagName, $attr, $value, $tagName );
			default :
				if (isset ( $post ['PARAM_order'] )) {
					$post ['PARAM_order'] = $post ['PARAM_order'] . " " . $post ['PARAM_order_t'];
				}
				unset ( $post ['PARAM_order_t'] );
				if (isset ( $post ['attach'] )) {
					$image = array_shift ( $post ['attach'] );
					list ( $id, $imageName ) = explode ( '|', $image );
					$map ['id'] = $id;
					$model = model ( 'Xattach' )->where ( $map )->field ( 'savepath,savename' )->find ();
					$post ['PARAM_image'] = SITE_URL . "/data/uploads/" . $model ['savepath'] . $model ['savename'];
				}
				
				$param = $post;
				$safetyArray = array ("title", "tplHeight", "limit", "words" );
				$safetyArray2 = array ("(","\\",")","<",">",'"',"'","]","[",";");
				foreach ( $param as $key => $value ) {
					if (strpos ( $key, 'PARAM_' ) !== false && ! empty ( $value )) {
						$key = str_replace ( 'PARAM_', '', $key );
						$value = str_replace($safetyArray2,"",$value);
						if (in_array ( $key, $safetyArray ))
							$value = t ( $value );
						$attr [] = $key . "=\"" . $value . "\"";
					}
				}
				if (isset ( $post ['tag'] ) && ! empty ( $post ['tag'] )) {
					$attr [] = model ( 'Xtag' )->getTagId ( $post ['tag'] );
				}
				
				if (isset ( $post ['head_link'] ) && ! empty ( $post ['head_link'] )) {
					$post ['head_link'] = str_replace ( '[@]', '&', $post ['head_link'] );
					$attr [] = "head_link=" . "\"" . htmlspecialchars ( json_encode ( $post ['head_link'] ) ) . "\"";
				}
				
				$attr = implode ( ' ', $attr );
		}
		return sprintf ( '<%s %s/>', $tagName, $attr );
	}
	public function getPopUp() {
		if (! model ( 'UserGroup' )->inGroup ( $this->mid, 1 ) && ! model ( 'UserGroup' )->inGroup ( $this->mid, 4 )) {
			$this->error ( '您没有管理权限' );
		}
		$parseTag = service ( 'ParseTag' );
		if (isset ( $_GET ['sign'] )) {
			$data = $parseTag->getTagInfo ( $_GET ['sign'] );
			$attr = $data ['attr'];
			$order = 'rTime DESC';
			switch ($_GET ['widgetId']) {
				case 2 :
					$affiche = M ()->table ( C ( 'DB_PREFIX' ) . "forum_topic as topic 
						left join " . C ( 'DB_PREFIX' ) . "forum_template_type as template
						on topic.special = template.id" )->field ( 'topic.*' )->where ( "topic.isdel = 0 and topic.affiche = 1" )->order ( $order )->findAll ();
					$this->assign ( 'affiche', $affiche );
					$this->assign('tree',$this->getForumTree($attr['fid']));
					break;
				case 3 :
					$this->assign ( 'linkData', json_decode ( $data ['content'] ) );
					break;
			}
			
			if (! empty ( $data ['content'] )) {
				$this->assign ( 'html', $data ['content'] );
			}
			list ( $o, $t ) = explode ( " ", $attr ['order'] );
			
			$attr ['order'] = $o;
			$attr ['order_t'] = $t;
			$attr ['content'] = str_replace ( '[@]', '&', $attr ['content'] );
			$attr ['head_link'] = json_encode ( $attr ['head_link'] );
			$attr ['title'] = isset ( $attr ['title'] ) ? $attr ['title'] : "";
			$this->assign ( 'attr', $attr );
		}
		//获取Tag名
		$popup = str_replace ( ':', "/", $_GET ['tagName'] );
		
		$path = SITE_PATH . '/addons/plugins/Tags/' . $popup . '/popUp.html';
		$this->assign ( 'id', $_GET ['id'] );
		$this->assign ( 'index', $_GET ['index'] );
		$this->assign ( 'parentId', $_GET ['parentId'] );
		$this->assign ( 'layout', $_GET ['needClass'] );
		$this->assign ( 'tagName', $_GET ['tagName'] );
		$this->display ( $path );
	}
	
	private function getForumTree($default){
		$cateService = service ( "Category" );
		$cate = $cateService->getCategoryList ();
		$selectOption = $this->replaceHtml($cate,0,$default);
		return $selectOption;
	}
	
	private function replaceHtml($cate,$level = 0,$default){
		$str = "";
		$level ++;
		foreach($cate as $value){
			if(isset($value['children'])){
				$str .= "<optgroup label=".str_repeat("&nbsp;",$level-1).$value['name'].">";
				$str .= $this->replaceHtml($value['children'],$level,$default);
				$str .= "</optgroup>";
			}else{
				if($level == 1){
					$str .= "<optgroup label=".str_repeat("&nbsp;",$level-1).$value['name'].">";
				}else{
					$str .=$this->replaceItem($value,$level-1,$default);
				}
			}
		}
		return $str;
	}
	private function replaceItem($data,$level,$default){
		$fid	=	explode(',',$default);
		if(in_array($data['fid'],$fid)){ $selected = 'selected="selected"';}
		return sprintf("<option ".$selected." value='%s'>%s</option>",$data['fid'],str_repeat("&nbsp;",$level).$data['name']);	
	}
	
	//管理员操作框中的操作功能
	public function action() {
		$action = t ( $_POST ['action'] );
		switch ($action) {
			case 'tip' :
				$this->tip ();
				break;
			case 'dist' :
				$this->dist ();
				break;
			case 'top' :
				$this->top ();
				break;
			case 'highlight' :
				$this->highlight ();
				break;
			case 'dump' :
				$this->dump ();
				break;
			case 'commend' :
				$this->commend ();
				break;
			case 'delete' :
				$this->delete ();
				break;
			case 'close' :
				$this->close ();
				break;
			case 'hide' :
				$this->hide ();
				break;
			case "deletePost" :
				$this->deletePost ();
				break;
			case "tip1" :
				$this->tip1 ();
				break;
			case "tip2" :
				$this->tip2 ();
				break;
			case "changeCate" :
				$this->changeCate ();
				break;
			case "hot" :
				$this->hot ();
				break;
			case "image" :
				$this->image ();
				break;
			case "attached" :
				$this->attached ();
				break;
			case "banzhu" :
				$this->banzhu ();
				break;
		}
		
		//处理标示符索引 - 删除关闭的标示符
		$check = $this->actionInit ( '', false );
		
		$tid = $check ['tid'];
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tempMap ['tid'] = $tid;
		} else {
			$map ['tid'] = $tempMap ['tid'] = array ('in', $tid );
		}
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
		$this->success ( "操作完成" );
	}
	
	//设置版块置顶操作
	private function tip1() {
		$check = $this->actionInit ( 'allow_tip' );
		$commend = intval ( $_POST ['commend'] );
		$topic = $check ['topic'];
		$tid = $check ['tid'];
		
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		switch ($topic [0] ['top']) {
			case 0 :
				$top = 1;
				break;
			case 1 :
				$top = 0;
				break;
			case 2 :
				$this->checkPopedom("allow_all_tip");
				$top = 1;
				break;
		}
		
		$save ['top'] = $top;
		
		$res = $this->topic->where ( $map )->save ( $save );
		$topicCount = count ( $topicCount );
		if ($res && $top == 1) {
//			$credit = M ( "user_credit" );
//			foreach ( $topic as $value ) {
//				$scoreMap ['pid'] = $value ['tid'];
//				$scoreMap ['action'] = "top";
//				$scoreSave ['pid'] = 0;
//				$credit->where ( $scoreMap )->save ( $scoreSave );
//			}
			foreach ( $topic as $value ) {
				$this->credit->setUserCredit($value ['uid'], 'forum_top');
			}
		} else {
			if ($top == 0) {
//				$credit = M ( "user_credit" );
//				foreach ( $topic as $value ) {
//					$scoreMap ['pid'] = $value ['tid'];
//					$scoreMap ['action'] = "top";
//					$scoreSave ['pid'] = 0;
//					$credit->where ( $scoreMap )->save ( $scoreSave );
//				}
			}
		}
		$this->_setSign ( 2, $top, $topic );
		D ( 'Log' )->put ( $this->mid, $this->class, "设置了" . $topicCount . "个帖子为版块置顶" );
	}
	
	//设置全局置顶操作
	private function tip2() {
		$check = $this->actionInit ( 'allow_all_tip' );
		$commend = intval ( $_POST ['commend'] );
		$topic = $check ['topic'];
		$tid = $check ['tid'];
		
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		switch ($topic [0] ['top']) {
			case 0 :
				$top = 2;
				break;
			case 1 :
				$top = 2;
				break;
			case 2 :
				$top = 0;
				break;
		}
		
		$save ['top'] = $top;
		
		$res = $this->topic->where ( $map )->save ( $save );
		$top = $top == 2 ? 1 : 0;
		
		if ($res && $top == 1) {
//			$credit = M ( "user_credit" );
//			foreach ( $topic as $value ) {
//				$scoreMap ['pid'] = $value ['tid'];
//				$scoreMap ['action'] = "top";
//				$scoreSave ['pid'] = 0;
//				$credit->where ( $scoreMap )->save ( $scoreSave );
//			}
			foreach ( $topic as $value ) {
				$this->credit->setUserCredit($value ['uid'], 'forum_top');
			}
		} else {
//			$credit = M ( "user_credit" );
//			foreach ( $topic as $value ) {
//				$scoreMap ['pid'] = $value ['tid'];
//				$scoreMap ['action'] = "top";
//				$scoreSave ['pid'] = 0;
//				$credit->where ( $scoreMap )->save ( $scoreSave );
//			}
		}
		$this->_setSign ( 1, 0, $topic );
		D ( 'Log' )->put ( $this->mid, $this->class, "设置了" . count ( $topic ) . "个帖子为全局置顶" );
	}
	
	//设置锁帖操作
	private function close() {
		$check = $this->actionInit ( 'allow_close' );
		$topic = $check ['topic'];
		if ($topic [0] ['lock']) {
			$close = 0;
		} else {
			$close = 1;
		}
		$tid = $check ['tid'];
		
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		$save ['lock'] = $close;
		$res = $this->topic->where ( $map )->save ( $save );
		if ($res && $close == 1) {
			$setting = D ( 'ForumSetting' );
			foreach ( $topic as $value ) {
//				$this->credit->setFid ( $value ['fid'] );
//				$setData = $setting->getBaseicSet ( $value ['fid'] );
//				$this->credit->setDummy ( $setData ['dummy_state'] == 1 );
//				$this->credit->setDummyId ( $value ['maskId'] );
//				$this->credit->setPid ( $value ['tid'] );
//				$this->credit->set ( $value ['uid'], "close" );
			}
		} else {
			if ($close == 0) {
//				$credit = M ( "user_credit" );
//				foreach ( $topic as $value ) {
//					$scoreMap ['pid'] = $value ['tid'];
//					$scoreMap ['action'] = "close";
//					
//					$scoreSave ['pid'] = 0;
//					$credit->where ( $scoreMap )->save ( $scoreSave );
//				}
			}
		}
		$this->_setSign ( 3, $close, $topic );
		D ( 'Log' )->put ( $this->mid, $this->class, "操作了" . count ( $topic ) . "个帖子是否为锁帖" );
	}
	
	//设置高亮操作
	private function highlight() {
		$check = $this->actionInit ( 'allow_highlight' );
		$tid = $check ['tid'];
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		$save ['highlight'] = t ( $_POST ['highlight'] );
		$res = $this->topic->where ( $map )->save ( $save );
		D ( 'Log' )->put ( $this->mid, $this->class, "高亮了" . count ( $check ['topic'] ) . "个帖子" );
	}
	
	//转移版块操作
	private function changeCate() {
		$check = $this->actionInit ( 'allow_changeCate' );
		$topic = $check ['topic'];
		$tid = $check ['tid'];
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		$save ['fid'] = intval ( $_POST ['newCate'] );
		$save ['tclass'] = 0;
		$this->topic->where ( $map )->save ( $save );
		$this->post->where ( $map )->save ( $save );
		D ( 'Log' )->put ( $this->mid, $this->class, "转移了" . count ( $topic ) . "个帖子" );
	}
	
	//设置推荐操作
	private function commend() {
		$check = $this->actionInit ( 'allow_commend' );
		$topic = $check ['topic'];
		$tid = $check ['tid'];
		$tid = array_filter ( $tid );
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		$commended = D ( 'Commend' )->where ( $map )->count ();
		if ($commended) {
			D ( 'Commend' )->where ( $map )->delete ();
			$commend = 0;
		} else {
			if (count ( $tid ) < 1) {
				D ( 'Commend' )->add ( $map );
			} else {
				$model = D ( 'Commend' );
				foreach ( $tid as $value ) {
					$add ['tid'] = $value;
					$model->add ( $add );
				}
			}
			$commend = 1;
		}
	
		if ($commend == 1) {
			$setting = D ( 'ForumSetting' );
			foreach ( $topic as $value ) {
				$this->credit->setUserCredit($value['uid'], 'forum_commend');
			}
		} else {
			if ($commend == 0) {
//				$credit = M ( "user_credit" );
//				foreach ( $topic as $value ) {
//					$scoreMap ['pid'] = $value ['tid'];
//					$scoreMap ['action'] = "commend";
//					$scoreSave ['pid'] = 0;
//					$credit->where ( $scoreMap )->save ( $scoreSave );
//				}
			}
		}
		
		$this->_setSign ( 23, $commend, $topic );
		D ( 'Log' )->put ( $this->mid, $this->class, "推荐了" . count ( $topic ) . "个帖子" );
	}
	
	//设置精华操作
	private function dist() {
		$check = $this->actionInit ( 'allow_elite' );
		$topic = $check ['topic'];
		$tid = $check ['tid'];
		if ($topic [0] ['dist']) {
			$elite = 0;
		} else {
			$elite = 1;
		}
		
		
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		$save ['dist'] = $elite;
		$res = $this->topic->where ( $map )->save ( $save );
		$setting = D ( 'ForumSetting' );

		if ($res && $elite == 1) {
			foreach ( $topic as $value ) {
				if (C ( 'MEMCACHED_ON' )) {
					$cache = service ( 'Cache' );
					$cache->cleanDist($value ['maskName']);	
				}
				$this->credit->setUserCredit($value ['uid'], 'forum_dist');
			}
		} else {
			if ($elite == 0) {
				$credit = M ( "user_credit" );
				foreach ( $topic as $value ) {
					$this->credit->setUserCredit($value ['uid'], 'forum_undist');
				}
			}
		}
		$this->_setSign ( 5, $elite, $topic );
		D ( 'Log' )->put ( $this->mid, $this->class, "设置了" . count ( $topic ) . "个帖子为精华帖" );
	}
	
	//设置版主正式回复操作
	private function banzhu() {
		$check = $this->actionInit ( 'allow_banzhu' );
		$topic = $check ['topic'];
		$tid = $check ['tid'];
		if ($topic [0] ['banzhu']) {
			$banzhu = 0;
		} else {
			$banzhu = 1;
		}
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		$save ['banzhu'] = $banzhu;
		$res = $this->topic->where ( $map )->save ( $save );
		$setting = D ( 'ForumSetting' );
		$this->_setSign ( 77, $banzhu, $topic );
		D ( 'Log' )->put ( $this->mid, $this->class, "设置了" . count ( $topic ) . "个帖子为版主已有正式回复" );
	}
	
	//设置热帖操作
	private function hot() {
		$check = $this->actionInit ( 'allow_edit_thread' );
		
		$topic = $check['topic'];
		$tid = $check['tid'];
		$sign = $topic[0]['sign'];
		if (in_array(24, explode(',', $sign))) {
			$hot = 0;
		} else {
			$hot = 1;
		}
		
		$this->_setSign(24, $hot, $topic);
		D('Log')->put($this->mid, $this->class, "操作了" . count ( $topic ) . "个帖子的热帖标识" );
	}
	
	//删除操作
	private function delete() {
		$check = $this->actionInit ( '', false );
		
		$topic = $check ['topic'];
		$tid = $check ['tid'];
		
		$this->class = $topic [0] ['fid'];
		$data = array();
		$data['postInfo']['tclass'] = $this->class;
		$data['postInfo']['uid']    = $topic[0]['uid'];
		$this->specialClassPopedom('allow_delete',"allow_delete_thread",$data,"删除");
		
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		$save ['isdel'] = 1;
		$result = $this->topic->where ( $map )->save ( $save );
		D ( 'Commend' )->where ( $map )->delete ();
		if ($result) {
			$this->post->where ( $map )->save ( $save );
		}
		
		if ($result) {
			$setting = D ( 'ForumSetting' );
			foreach ( $topic as $value ) {
				$this->credit->setUserCredit($value ['uid'], 'forum_delete_post');
			}
		}
		$map ['tid'] = $map ['tid'];
		if ($result) {
			D ( 'Log' )->put ( $this->mid, $this->class, "删除了" . count ( $topic ) . "个主题" );
			$this->success ( U ( 'forum/List/index', array ("class" => $this->class ) ) );
		} else {
			$this->error ( "操作失败" );
		}
	}
	
	private function image() {
		$check = $this->actionInit ( 'allow_edit_thread' );
		
		$topic = $check ['topic'];
		$tid = $check ['tid'];
		$sign = $topic [0] ['sign'];
		
		if (in_array ( 25, explode ( ',', $sign ) )) {
			$hot = 0;
		} else {
			$hot = 1;
		}
		
		$this->_setSign ( 25, $hot, $topic );
		D ( 'Log' )->put ( $this->mid, $this->class, "操作了" . count ( $topic ) . "个帖子的图片标识" );
	}
	
	private function attached() {
		$check = $this->actionInit ( 'allow_edit_thread' );
		
		$topic = $check ['topic'];
		$tid = $check ['tid'];
		$sign = $topic [0] ['sign'];
		
		if (in_array ( 27, explode ( ',', $sign ) )) {
			$hot = 0;
		} else {
			$hot = 1;
		}
		
		$this->_setSign ( 27, $hot, $topic );
		D ( 'Log' )->put ( $this->mid, $this->class, "操作了" . count ( $topic ) . "个帖子的附件标识" );
	}
	
	//设置内部可见操作
	private function hide() {
		$check = $this->actionInit ( 'allow_hide' );
		
		$topic = $check ['topic'];
		$tid = $check ['tid'];
		if ($topic [0] ['hide']) {
			$hide = 0;
		} else {
			$hide = 1;
		}
		if (count ( $tid ) < 1) {
			$map ['tid'] = $tid;
		} else {
			$map ['tid'] = array ('in', $tid );
		}
		$save ['hide'] = $hide;
		$res = $this->topic->where ( $map )->save ( $save );
		$this->_setSign ( 4, $hide, $topic );
		D ( 'Log' )->put ( $this->mid, $this->class, "操作了" . count ( $topic ) . "个帖子是否仅内部可见" );
	}
	
	//获取用户的昵称
	private function setMaskInfo() {
		$res = M('user')->field('uid, uname')->where('uid='.$this->mid)->find();
		return $res;
	}
	
	public function doAddMark(){
		$banzhu = intval($_POST['banzhu']);
		$map['pid'] = intval($_POST['pid']);
		$tid = intval($_POST['tid']);
		$fid = intval($_POST['fid']);
		$data['banzhu'] = $banzhu? 0:1;
		$res = M('forum_post')->where($map)->save($data);
		if($res){
			$this->memCache($tid,$fid);
			echo $res;
		}
	}
}
?>