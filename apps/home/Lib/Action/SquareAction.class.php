<?php
class SquareAction extends Action
{
	private $appName;
	private $event;
	public function _initialize()
	{
		// 验证是否允许匿名访问微博广场
		if ($this->mid <= 0 && intval(model('Xdata')->get('siteopt:site_anonymous_square')) <= 0) {
			redirect(U('home'));
		}

        $setinfo[0] = "before";
        $setinfo[1] = "start";
        $setinfo[2] = "end";
        $this->assign("setinfo",$setinfo);
        $expend_menu = array();
        Addons::hook('home_square_tab', array('menu' => & $expend_menu));
        $this->assign('expend_menu', $expend_menu);
	}

    // 有不存在的ACTION操作的时候执行
    protected function _empty() {
    	$this->display('addons');
    }

    //广场 首页
    public function index(){
    	$cacheTime = 600;
    	// 今日看点
    	$data = S('S_square_xdata');
    	if(empty($data)) {
    		$res = model('Xdata')->lget('weibo');
	    	$data['aboutkey']        = $res['todaytopic'];
	    	$data['aboutkey_id']	 = M('weibo_topic')->getField('topic_id',"name='{$data['aboutkey']}'");
	    	$data['aboutkey_follow'] = getFollowState($this->mid,array('name'=>$data['aboutkey']),1);
	    	
	    	S('S_square_xdata', $data, $cacheTime);
    	}
    	
    	$user_model       = D('User', 'home');
    	$user_count_model = model('UserCount');

    	// 今日看点相关的用户
    	$data['userlist'] = S('S_square_userlist');
    	if(empty($data['userlist'])) {
	    	//$data['userlist'] = M('weibo')->where("transpond_id=0 AND isdel=0 AND content LIKE '%".$data['aboutkey']."%'")->limit(10)->order('ctime DESC')->findAll();
    		$topic_id = D('Topic','weibo')->getTopicId(t($data['aboutkey']));
    		$topic_list = M('WeiboTopicLink')->where("topic_id=".$topic_id)->limit(10)->findAll();
    		$weibo_ids = getSubByKey($topic_list, 'weibo_id');
    		$map['weibo_id'] = array('IN',$weibo_ids);
	    	$data['userlist'] = M('weibo')->where($map)->findAll();
	    	$uids = getSubByKey($data['userlist'], 'uid');
	    	if (!in_array($this->mid, $uids))
	    		$uid = array_merge($this->mid);
	    	$user_model->setUserObjectCache($uids);
	    	$user_count_model->setUserFollowingCount($uids);
	    	$user_count_model->setUserFollowerCount($uids);
	    	unset($uids);
			foreach ($data['userlist'] as $key => $value) {
				$data['userlist'][$key]['userinfo']  = $user_model->getUserByIdentifier($value['uid']);
				$data['userlist'][$key]['following'] = $user_count_model->getUserFollowingCount($value['uid']);
				$data['userlist'][$key]['follower']  = $user_count_model->getUserFollowerCount($value['uid']);
				//$data['userlist'][$key]['followState']  = getFollowState( $this->mid , $value['uid'] );
			}
			
			S('S_square_userlist', $data['userlist'], $cacheTime);
    	}

		// 搜索热词
		if (count($data['hotTopic'])>3) {
			$data['hotkeys'] = $this->_getRandomSubArray($data['hotTopic'],3);
		}else {
			$data['hotkeys'] = $data['hotTopic'];
		}

		// 活跃用户
		$data['hotUser'] = S('S_square_hotUser');
		global $ts;
		//$huNumPerRow = $ts['site']['site_theme']=='weibo'?4:4;
		$huNumPerRow = 4;
		if(empty($data['hotUser'])) {	
			$hotUserNum  = $huNumPerRow*10;
	    	$time_range = model('Xdata')->get('square:hotuser');
	    	if(!is_numeric($time_range) || $time_range<1)$time_range = 1;
			$today       = mktime(0,0,0,date("m"),date("d"),date("Y"));
			$yesterday   = $today-$time_range*24*3600;
			$db_prefix  = C('DB_PREFIX');
	    	$hotUser = M()->query("SELECT uid,count(weibo_id) as weibo_num FROM {$db_prefix}weibo where ctime>{$yesterday} AND ctime<{$today} AND isdel=0 GROUP BY uid ORDER BY weibo_num DESC LIMIT {$hotUserNum}");
	    	if($hotUser){
		    	$data['hotUserSlide'] = count($hotUser)>$huNumPerRow?1:0;
		    	$uids = getSubByKey($hotUser, 'uid');
		    	$user_model->setUserObjectCache($uids);
		    	$user_count_model->setUserFollowerCount($uids);
		    	unset($uids);
		    	foreach ($hotUser as $key=>$value) {
		    		$hotUserRow = ceil(($key+1)/$huNumPerRow);
			    	$data['hotUser'][$hotUserRow][$key] = $user_model->getUserByIdentifier($value['uid']);
			    	$data['hotUser'][$hotUserRow][$key]['follower'] = $user_count_model->getUserFollowerCount($value['uid']);
		    	}
			}else{
				$data['hotUser'] = '';
			}
	    	S('S_square_hotUser', $data['hotUser'], $cacheTime);
		}else{
			$data['hotUserSlide'] = count($data['hotUser']) > 1? 1:0;
		}
    	//专家推荐：是否具有专家
    	$data['star_list'] = S('S_square_star_list');
    	if(empty($data['star_list'])) {
	    	$star = D('weibo_star')->find();
	    	if ($star)
	    		$data['star_list'] = 1;
	    		
	    	S('S_square_star_list', $data['star_list'], $cacheTime);
    	}

		//粉丝与关注情况
		$data['followInfo'] = S('S_square_followInfo');
		if(empty($data['followInfo'])) {
			$data['followInfo'] = array(
				'following' => $user_count_model->getUserFollowingCount($this->mid),
				'follower'  => $user_count_model->getUserFollowerCount($this->mid)
			);
			
			S('S_square_followInfo', $data['followInfo'], $cacheTime);
		}

		// 粉丝榜
	   	$data['topfollow'] = D('Follow', 'weibo')->getTopFollowerUser();

    	// 底部微博Tab
    	$data['square_list_menu'] = array(
    								    '' => L('other_say'),
    									'transpond' => L('hot_transmit'),
    									'comment' => L('hot_reply')
    				  				);
    	Addons::hook('home_square_index_list_tab', array(&$data['square_list_menu']));

    	$this->assign($data);
    	$this->setTitle(L('hot_weibo'),L('hot_weibo'),L('hot_weibo'));
    }

    //微访谈主页
    public function interview($param) {
    	$order = NULL;
    	switch( $_GET['order'] ) {
    		case 'new':    //最新排行
    			$order = 'cTime DESC';
    			$this->setTitle('最新' . $this->appName);
    			break;
    		case 'following':    //关注的人的
    			$following = M('weibo_follow')->field('fid')->where("uid={$this->mid} AND type=0")->findAll();
    			foreach($following as $v) {
    				$in_arr[] = $v['fid'];
    			}
    			$map['uid'] = array('in',$in_arr);
    			$this->setTitle('我关注的人的' . $this->appName);
    			break;
    		default:      //默认热门排行
    			$order = 'joinCount DESC,attentionCount DESC,cTime DESC';
    			$this->setTitle('微访谈' . $this->appName);
    	}
    	
    	//查询
    	$title = t($_POST['title']);
    	if ($_POST['title']) {
    		$map['title'] = array( 'like',"%".t($_POST['title'])."%" );
    		$this->setTitle('搜索' . $this->appName);
    	}
    	if ($_GET['cid']) {
    		$map['type']  = intval($_GET['cid']);
    		$this->setTitle('分类浏览');
    	}
    	
    	//$result  = $this->event->getEventList($map,$order,$this->mid);
    	
    	//微访谈内容取数
    	$res = D('Topics','weibo')->getWonderful();
    	$result['arr'] = $res;
        
        $list = D('Topics','weibo')->getInterview();
        $result['list'] = $list;
        
        
        $userId = M('weibo_recomchart')->limit(2)->select();
    	foreach($userId as $key=>$oneId){
    	    $data = getUserInfo($oneId['user_id']);
            $data['introduce'] = $oneId['introduce'];
            $data['location']       =   getLocation($data['province'],$data['city']);
            if(!$data['location'])  $data['location'] ='<br />';
            $data['following_url']  =   U('home/Space/follow',array('type'=>'following','uid'=>$oneId['user_id']));
            $data['follower_url']   =   U('home/Space/follow',array('type'=>'follower','uid'=>$oneId['user_id']));
            $data['space_url']      =   U('home/Space/index',array('uid'=>$oneId['user_id']));
            $data['space_link']     =   getUserSpace($oneId['user_id'],'nocard','_blank');
            $data['follow_state']   =   ($this->mid==$oneId['user_id'])?'self':D('Follow','weibo')->getState($this->mid, $oneId['user_id'], 0);
            $keyinfo[$key]=$data;
        }
        //获取用户参加的微访谈
        $db_prefix  =  C('DB_PREFIX');
        $my_detail = M('')->table("{$db_prefix}weibo_problem AS problem LEFT JOIN {$db_prefix}weibo_topics AS topics ON topics.topics_id=problem.topics_id left join {$db_prefix}weibo_topic as topic on topics.topic_id = topic.topic_id")
                    ->where("problem.user_id = ".$this->mid)->order("topics.status")->field("DISTINCT topics.*")->select();
        $this->assign("on_me",$my_detail);
        $interview_info = M('weibo_topics')->where("isvisit = 1 AND iswonderful = 1")->order("topics_id")->limit(2)->select();
        $this->assign("pass_topics",$interview_info);
        $this->assign("author",$keyinfo);
    	
    	
    	
    	$this->assign($result);
    	$this->display();
    } 
    
    //微访谈列表
    public function interviewList(){
    	$status =  intval($_GET['status']);
        $this->assign("get_status",$status);
    	$arr = D('Topics','weibo')->getInterviewList($status);
    	$this->assign($arr);
    	$this->display();
    	
    }
    
    //精彩访谈列表
    public function wonderfulView(){
    	 
    	$res = D('Topics','weibo')->getWonderfulList();
    	//dump(D('Topics','weibo'));
    	 
    	$data['arr'] = $res;
    	$this->assign($data);
    	 
    	 
    	$this->display();
    	 
    }
    
    
    //微访谈页面
    public function interviewPage(){
     	$data['search_key'] = $this->__getSearchKey ();
        mb_convert_encoding($str, "utf-8", "gb2312");
        $db_prefix  = C('DB_PREFIX');
    	 Session::pause();
    	 if (false == $data['topics'] = D('Topics', 'weibo')->getTopics($data['search_key'], $_GET['id'], $_GET['domain'], 1)) {
    		if (null == $data['search_key']) {
    			$this->error(L('special_not_exist'));
    		}
    		$data['topics']['name'] = t($data['search_key']);
    	}  
    	$this->assign("topicId",$data['topics']['topics_id']);
        $this->assign("topics_info",$data['topics']);
        $this->assign("search_key",$data['search_key']);
    	$data['search_key'] = $data['search_key'] ? $data['search_key'] : html_entity_decode($data['topics']['name'], ENT_QUOTES);
    	$data['search_key_id'] = $data['topics']['topic_id'] ? $data['topics']['topic_id'] : D('Topic', 'weibo')->getTopicId($data['search_key']);
    	
    	$data['followState'] = D ('Follow', 'weibo')->getTopicState ($this->mid, $data['search_key']);
    	// 其他关注该话题的人
    	$data['other_following'] = D('Follow', 'weibo')->field('uid')
    	->where("uid<>{$this->mid} AND fid={$data['search_key_id']} AND type=1")
    	->limit(9)->findAll();
    	
        // 微博Tab
        $data['weibo_menu'] = array(
                ''  => L('all'),
                'original' => L('original'),
        );
        Addons::hook('home_index_weibo_tab', array(&$data['weibo_menu']));
        
        $this->setTitle ( L('special').$data ['search_key']);
        $data['search_key'] = h(t($data['search_key']));
        
        //获取嘉宾列表
        $guestlist = M("weibo_character")->where("topics_id = ".$data['topics']['topics_id']." AND user_type = '1'")->select();
        foreach ($guestlist as $key => $value) {
            $info[$key] = $value['user_id'];
            $guests[$key] = getUserInfo($value['user_id']);
            $guests[$key]['follow_state']   =   ($this->mid==$value['user_id'])?'self':D('Follow','weibo')->getState($this->mid, $value['user_id'], 0);
            $guests[$key]['space_link']     =   getUserSpace($value['user_id'],'nocard','_blank');
            $guests[$key]['introduce'] = $value['introduce'];
        }
        //获取嘉宾列表
        $hostlist = M("weibo_character")->where("topics_id = ".$data['topics']['topics_id']." AND user_type = '2'")->select();
        foreach ($hostlist as $key => $value) {
            $hostinfo[$key] = $value['user_id'];
            $hosts[$key] = getUserInfo($value['user_id']);
            $hosts[$key]['follow_state']   =   ($this->mid==$value['user_id'])?'self':D('Follow','weibo')->getState($this->mid, $value['user_id'], 0);
            $hosts[$key]['space_link']     =   getUserSpace($value['user_id'],'nocard','_blank');
            $hosts[$key]['introduce'] = $value['introduce'];
        }
        //获取精彩回顾
        
        $this->assign("guestdata",$info);
        $this->assign("hostdata",$hostinfo);
        $this->assign("hosts",$hosts);
        $this->assign("guests",$guests);
        //排序类型
        /*if($_GET['order_type']=="idsort"){
            $order = "problem_id";
        }else if($_GET['order_type']=="iddesc"){
            $order = "problem_id desc";
        }else */
        if($_GET['order_type']=="notreplydesc"){
            $order = "down";
            $status = "notanswer";
        }else if($_GET['order_type']=="notreplysort"){
            $order = "up";
            $status = "notanswer";
        }else if($_GET['order_type']=="replyeddesc"){
            $order = "down";
            $status = "answered";
        }else if($_GET['order_type']=="replyedsort"){
            $order = "up";
            $status = "answered";
        }else if($_GET['order_type']=="allsort"){
            $order = "down";
        }else if($_GET['order_type']=="asksort"){
            $order = "content_time";
        }else if($_GET['order_type']=="askdesc"){
            $order = "content_time desc";
        }else if($_GET['order_type']=="answersort"){
            $order = "reply_time";
        }else if($_GET['order_type']=="answerdesc"){
            $order = "reply_time desc";
        }
        //获取微博类型,四种类型tab
    	if($_GET['type_to']=="all"){
    	   $data['list'] = D ( 'Operate', 'weibo' )->doSearchWithTopic ( "#{$data['topics']['name']}#", $ordertype , $order);
    	}else if($_GET['type_to']=="guest"){
    	    $data['list'] = D("Problem")->GetGuestList($data['topics']['topics_id'],$this->mid,$status,$order);
            foreach ($data['list']['data'] as $key => $value) {
               $info = D("Weibo","weibo")->getOneLocation($value['weibo_id']);
               $data['list']['data'][$key] = array_merge($data['list']['data'][$key],$info);
            }
    	}else if($_GET['type_to']=="host"){
            $data['list'] = D("Problem")->GetHostList($data['topics']['topics_id'],$status,$order);
            foreach ($data['list']['data'] as $key => $value) {
               $info = D("Weibo","weibo")->getOneLocation($value['weibo_id']);
               $data['list']['data'][$key] = array_merge($info,$data['list']['data'][$key]);
            }
        }else if($_GET['type_to']=="question"){
            $data['list'] = D("Problem")->GetAllList($data['topics']['topics_id'],$order);
            foreach ($data['list']['data'] as $key => $value) {
                $info = D("Weibo","weibo")->getOneLocation($value['weibo_id']);
                $data['list']['data'][$key] = array_merge($info,$data['list']['data'][$key]);
            }
        }
        $this->assign("type_to",$_GET['type_to']);
        $this->assign ( $data );
    	$this->display("interviewStart");
    }
    
    
       private function __getSearchKey() {
        $key = '';
        // 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (isset ( $_REQUEST ['k'] ) && ! empty ( $_REQUEST ['k'] )) {
            if ($_GET ['k']) {
                $key = html_entity_decode ( urldecode ( $_GET ['k'] ), ENT_QUOTES );
            } elseif ($_POST ['k']) {
                $key = $_POST ['k'];
            }
            // 关键字不能超过200个字符
            if (mb_strlen ( $key, 'UTF8' ) > 200)
                $key = mb_substr ( $key, 0, 200, 'UTF8' );
            $_SESSION ['home_user_search_key'] = serialize ( $key );

        } else if (is_numeric ( $_GET [C ( 'VAR_PAGE' )] )) {
            $key = unserialize ( $_SESSION ['home_user_search_key'] );

        } else {
            //unset($_SESSION['home_user_search_key']);
        }
		$key = str_replace(array('%','\'','"','<','>'),'',$key);
        return trim ( $key );
    }
    /*
     *获取当前问题的嘉宾列表，并进行转移
     * @time:2012.11.09
     * @author:jianjungki
     */
    public function transfer(){
        $map['topics_id'] = $_GET['topicid'];
        $map['user_type'] = '1';
        $guests = M('weibo_character')->where($map)->findAll();
        foreach ($guests as $key => $value) {
            $guests[$key] = getUserInfo($value['user_id']);
        }
        $map2['guest_id'] = array('exp',"is not null");
        $map2['topics_id'] = $_GET['topicid'];
        $map2['weibo_id'] = $_GET['weiboid'];
        $guest_now = D('Problem')->where($map2)->findAll();
        $this->assign("user_id",$guest_now[0]['user_id']);
        $str = "";
        foreach ($guest_now as $key => $value) {
            $str .= getUserSpace($value['guest_id'])." ";
        }
        $this->assign("guest_now",$str);
        $this->assign("topic_id",$_GET['topicid']);
        $this->assign("weibo_id",$_GET['weiboid']);
        $this->assign("guestlist",$guests);
        //dump($guests);
        $this->display();
    }
    /*
     *转移嘉宾操作 
     * @time:2012.11.09
     * @author:jianjungki
     */
    function TransferGuest(){
        $Interview = D("Problem");
        $map['guest_id'] = $_POST['guestId'];
        $map['weibo_id']=$_POST['weiboid'];
        $map['topics_id']=$_POST['topicid'];
        if(!$Interview->where($map)->find()){
            $map['content_type'] = 1;
            $map['user_id'] = $_POST['userid'];
            if($Interview->data($map)->add())
                echo 1;
            else
                echo 2;
            //提示嘉宾
            D('Weibo','weibo')->notifyToAtme($map['user_id'], $map['weibo_id'], '',$map['guest_id']);
            return;
        }else{
            echo 0;
            return;
        }
        
    }
    /*
     * 推荐给朋友
     */
    public function toFriends(){
        $name = $_POST['topic_name'];
        $tofriends = explode(" ", $_POST['friendsId']);
        $message_data['title']   = "邀您参加微访谈#".$name."#";
        $url = '<a href="' . U('home/Square/interviewPage', array('k'=>$name,'type_to'=>'all')).'" target="_blank">去看看</a>';
        $message_data['content'] = "邀您参加微访谈“#{$name}#” ，点击   " . $url . '进入。';
        foreach ($tofriends as $t_u_k => $t_u_v) {
            $message_data['to']      = $t_u_v;
            $res = model('Message')->postMessage($message_data,  $this->mid);
            if ($res) {
                unset($tofriends[$t_u_k]);
            }else{
                $this->error("邀请失败");
            }
        }
        $this->success("邀请成功");
    }
    //删除微访谈记录
    public function DelInterview(){
        if(D( 'Problem' )->where( 'weibo_id='.intval(($_POST['id'])) )->find()){
            $res = D( 'Problem' )->where( 'weibo_id='.intval(($_POST['id'])) )->delete();//删除相应的问题
        }
        if(!res){echo 0;return;}
        $arrWeiInfo = D( 'Operate','weibo')->where( 'weibo_id='.intval(($_POST['id'])) )->field('isdel')->find();
        if( !$arrWeiInfo['isdel'] ){
            if( D('Operate','weibo')->deleteMini( intval($_POST['id']) , $this->mid ) ){
                X('Credit')->setUserCredit($this->mid,'delete_weibo');
                
                echo '1';
            }
        }else{
            echo '1';
        }
    }
    //设置微访谈不可见
    public function Display_Interview(){
        $num = D('Problem')->NotDisplay($_POST['weibo_id']);
        echo $num;
    }
    
	//天工广场
    public function tgSquare(){
    	
    	$apps = model('App')->where('is_recommend=1')->order('rec_time desc')->limit(9)->select();
        $this->assign('apps', $apps);
    	
    	$obj = D('Topics','weibo');
    	$arr = $obj->getTopicList();

    	$cacheTime = 600;
    	// 今日看点
    	$data = S('S_square_xdata');
    	if(empty($data)) {
    		$res = model('Xdata')->lget('weibo');
	    	$data['aboutkey']        = $res['todaytopic'];
	    	$data['aboutkey_id']	 = M('weibo_topic')->getField('topic_id',"name='{$data['aboutkey']}'");
	    	$data['aboutkey_follow'] = getFollowState($this->mid,array('name'=>$data['aboutkey']),1);
	    	
	    	S('S_square_xdata', $data, $cacheTime);
    	}
    	
    	$user_model       = D('User', 'home');
    	$user_count_model = model('UserCount');

    	// 今日看点相关的用户
    	$data['userlist'] = S('S_square_userlist');
    	if(empty($data['userlist'])) {
	    	$data['userlist'] = M('weibo')->where("transpond_id=0 AND isdel=0 AND content LIKE '%".$data['aboutkey']."%'")->limit(10)->order('ctime DESC')->findAll();
	    	$uids = getSubByKey($data['userlist'], 'uid');
	    	if (!in_array($this->mid, $uids))
	    		$uid = array_merge($this->mid);
	    	$user_model->setUserObjectCache($uids);
	    	$user_count_model->setUserFollowingCount($uids);
	    	$user_count_model->setUserFollowerCount($uids);
	    	unset($uids);
			foreach ($data['userlist'] as $key => $value) {
				$data['userlist'][$key]['userinfo']  = $user_model->getUserByIdentifier($value['uid']);
				$data['userlist'][$key]['following'] = $user_count_model->getUserFollowingCount($value['uid']);
				$data['userlist'][$key]['follower']  = $user_count_model->getUserFollowerCount($value['uid']);
			}
			
			S('S_square_userlist', $data['userlist'], $cacheTime);
    	}

		// 关注的话题
	    $data['followTopic'] =  D('Follow','weibo')->getTopicList($this->mid);

		// 搜索热词
		if (count($data['hotTopic'])>3) {
			$data['hotkeys'] = $this->_getRandomSubArray($data['hotTopic'],3);
		}else {
			$data['hotkeys'] = $data['hotTopic'];
		}

		// 活跃用户
		$data['hotUser'] = S('S_square_hotUser');
		global $ts;
		$huNumPerRow = 4;
		if(empty($data['hotUser'])) {	
			$hotUserNum  = $huNumPerRow*10;
	    	$time_range = model('Xdata')->get('square:hotuser');
	    	if(!is_numeric($time_range) || $time_range<1)$time_range = 1;
			$today       = mktime(0,0,0,date("m"),date("d"),date("Y"));
			$yesterday   = $today-$time_range*24*3600;
			$db_prefix  = C('DB_PREFIX');
	    	$hotUser = M()->query("SELECT uid,count(weibo_id) as weibo_num FROM {$db_prefix}weibo where ctime>{$yesterday} AND ctime<{$today} AND isdel=0 GROUP BY uid ORDER BY weibo_num DESC LIMIT {$hotUserNum}");
	    	if($hotUser){
		    	$data['hotUserSlide'] = count($hotUser)>$huNumPerRow?1:0;
		    	$uids = getSubByKey($hotUser, 'uid');
		    	$user_model->setUserObjectCache($uids);
		    	$user_count_model->setUserFollowerCount($uids);
		    	unset($uids);
		    	foreach ($hotUser as $key=>$value) {
		    		$hotUserRow = ceil(($key+1)/$huNumPerRow);
			    	$data['hotUser'][$hotUserRow][$key] = $user_model->getUserByIdentifier($value['uid']);
			    	$data['hotUser'][$hotUserRow][$key]['follower'] = $user_count_model->getUserFollowerCount($value['uid']);
		    	}
			}else{
				$data['hotUser'] = '';
			}
	    	S('S_square_hotUser', $data['hotUser'], $cacheTime);
		}else{
			$data['hotUserSlide'] = count($data['hotUser']) > 1? 1:0;
		}
    	//专家推荐：是否具有专家
    	$data['star_list'] = S('S_square_star_list');
    	if(empty($data['star_list'])) {
	    	$star = D('weibo_star')->find();
	    	if ($star)
	    		$data['star_list'] = 1;
	    		
	    	S('S_square_star_list', $data['star_list'], $cacheTime);
    	}

		//粉丝与关注情况
		$data['followInfo'] = S('S_square_followInfo');
		if(empty($data['followInfo'])) {
			$data['followInfo'] = array(
				'following' => $user_count_model->getUserFollowingCount($this->mid),
				'follower'  => $user_count_model->getUserFollowerCount($this->mid)
			);
			
			S('S_square_followInfo', $data['followInfo'], $cacheTime);
		}

		// 粉丝榜
	   	$data['topfollow'] = D('Follow', 'weibo')->getTopFollowerUser();
    	$uids = getSubByKey($data['topfollow'], 'uid');
    	$user_model->setUserObjectCache($uids);

    	// 底部微博Tab
    	$data['square_list_menu'] = array(
    								    '' => L('other_say'),
    									'transpond' => L('hot_transmit'),
    									'comment' => L('hot_reply')
    				  				);
    	Addons::hook('home_square_index_list_tab', array(&$data['square_list_menu']));

		//工程圈取数
		// $data['grouplist'] = D('Group','group')->order('rand()')->select();
		$data['tglist'] = $this->tgList();
		$data['arrss'] = $arr;
		//dump($data);
		// dump($data['list']);
    	$this->assign($data);
    	$this->setTitle(L('square').date('Y'.L('year').'m'.L('month').'d'.L('day')).L('hot_weibo'));
    	$this->display();
    }

    //广场-专家推荐
    public function index_star() {
		$star_list = D('Star','weibo')->getAllStart();
    	if (count($star_list) > 6) {
			$star_list = $this->_getRandomSubArray($star_list,6);
		}

    	if ($star_list) {
    		/*
    		 * 缓存用户数据
    		 */
    		$uids = getSubByKey($star_list, 'uid');
			D('User', 'home')->setUserObjectCache($uids);


    	}
		$this->assign('star_list',$star_list);
	    $this->display();
    }

    //广场-首页的微博列表
	public function index_weibo(){
		$data['type'] = $_GET['type'] ? $_GET['type'] : 'index';
		$map = '';
    	switch ($data['type']) {
    		case 'transpond':
    			$time_range = model('Xdata')->get('square:comment');
    			if(!is_numeric($time_range) || $time_range<1)$time_range = 7;
				$now        = time();
				$yesterday  = mktime(0,0,0,date("m"),date("d"),date("Y"))-$time_range*24*3600;
				$map = " ctime>{$yesterday} AND ctime<{$now} ";
    			$order = 'transpond DESC';
    			break;
    		case 'comment':
    			$time_range = model('Xdata')->get('square:comment');
    			if(!is_numeric($time_range) || $time_range<1)$time_range = 7;
				$now        = time();
				$yesterday  = mktime(0,0,0,date("m"),date("d"),date("Y"))-$time_range*24*3600;
				$map = " ctime>{$yesterday} AND ctime<{$now} ";
    			$order = 'comment DESC';
    			break;
    		case 'index':
    			$order = 'weibo_id DESC';
    			break;
    		default:
	    		$this->assign($data);
	    		$this->display();
    			exit;
    	}
    	$data['list'] = D('Operate','weibo')->doSearchTopic($map,$order,$this->mid);
		
		//是否已经归类
		$work_list = M("work_task")->where('app_type = 1')->select();
		$work_wid = getSubByKey($work_list, 'app_id');
		foreach( $data['list']['data'] as $key=>$value){
            $value['is_work'] = in_array($value['weibo_id'], $work_wid);
			$data['list']['data'][$key] = $value;
        }
		
    	if($data['list']){
	    	$this->assign($data);
	    	$this->display();
    	}else{
    		echo -1;
    	}
	}

    public function hot_topics(){
    	//热门话题榜
        $data['hotTopic'] =  D('Topic','weibo')->getHot();
		//热门话题推荐
		$re_topic_num = 3;
		if(count($data['hotTopic'])>$re_topic_num){
			for($i=0;$i<$re_topic_num;$i++){
				$data['re_hot_topic'][$i] = $data['hotTopic'][$i];
			}
		}else{
			$data['re_hot_topic'] = $data['hotTopic'];
		}

		$uids = array();
		foreach($data['re_hot_topic'] as &$rh) {
			$rh['data'] = D('Operate','weibo')->where("content LIKE '%".addslashes($rh['name'])."%' AND isdel=0")->order('weibo_id DESC')->limit(3)->findAll();
			$uids = array_merge($uids, getSubByKey($rh['data'], 'uid'));
			$rh['follow'] = getFollowState($this->mid,$rh,1);
			if(is_array($rh['data'])){
				$weibo_ids = getSubByKey($rh['data'], 'weibo_id');
				foreach($rh['data'] as &$v){
					$v['is_favorited'] = D('Favorite','weibo')->isFavorited($v['weibo_id'], $this->mid, $weibo_ids);
	    			$v = D('Operate','weibo')->getOne('',$v);
	    		}
			}
		}

		D('User','home')->setUserObjectCache($uids);

		$this->assign($data);
		$this->setTitle(L('hot_topic'));
    	$this->display();
    }

    public function star(){
		$starDao = D('Star','weibo');
		$data['group_list'] = $starDao->getAllGroupList();
		$data['user_list']  = $starDao->getStarsByGroup('',$this->mid);
		// dump($data['user_list']);
		$this->assign($data);
		$this->assign('showCount',12);
    	$this->setTitle(L('hall_of_fame').date('Y'.L('year').'m'.L('month')).L('fame_weibo'));
    	$this->display();
    }

    public function top(){

		//热门话题榜 - 按话题提及次数
		$data['top_topics'] = D('Topic', 'weibo')->getHot();
		//月度话题榜 - 月度新话题提及次数
		$data['top_monthly_topics'] = D('Topic', 'weibo')->getHot('auto',30);

		//微博粉丝榜 - 根据粉丝数
    	$data['top_users_by_follower'] = D('Follow', 'weibo')->getTopFollowerUser();
    	$uids = getSubByKey($data['topfollow'], 'uid');
		D('User', 'home')->setUserObjectCache($uids);

		//人气排行榜 - 按权重提取 粉丝数*a-转发数*b-评论数*c
		$data['top_users_by_rating'] =	D('Weibo','weibo')->getAllStarByMaxrating();

		$this->assign($data);
		$this->setTitle(L('top_list').date('Y'.L('year').'m'.L('month')).L('hot_pop_topic'));
    	$this->display();
    }

	/**
	 * 随机获取数组的单元
	 *
	 * @param array $source_array 原数组
	 * @param int   $numOfRequst  要获取的单元数量
	 * @return array
	 */
	protected function _getRandomSubArray($source_array, $numOfRequst = 1) {
		$res		= array();
		$random_id	= array_rand($source_array, $numOfRequst);
		foreach($random_id as $v) {
			$res[]	= $source_array[$v];
		}
		return $res;
	}
	
	protected function tgList()
	{
		if ($this->mid) {
			$map = ' uid = ' . $this->mid . ' and ';
		}	
		//行业主管单位
		$tglist['manage_org']['title'] = '政府与行业协会';
		$tglist['manage_org']['data'] = M()->query("SELECT u.uid,u.uname,wb.* FROM ts_user u LEFT JOIN (SELECT fid FROM ts_weibo_follow WHERE {$map} TYPE = 0)  wb ON wb.fid=u.uid 
                    WHERE deputyoriid IS NOT NULL AND u.uid<>1 AND u.isGov=1 GROUP BY uid ORDER BY fid");
				
		//专家
		$star_list = D('Star','weibo')->getAllStart();
    	// if (count($star_list) > 10) {
			// $star_list = $this->_getRandomSubArray($star_list,10);
		// }
		$tglist['pro_user']['title'] = '领域专家风采';
		$tglist['pro_user']['data'] = M()->query("SELECT u.uid,u.uname,wb.* FROM ts_user u LEFT JOIN (SELECT fid FROM ts_weibo_follow WHERE {$map} TYPE = 0)  wb ON wb.fid=u.uid 
                    WHERE deputyoriid IS NULL AND u.uid<>1 AND u.isExpert=1 GROUP BY uid ORDER BY fid");
		
		//工程圈取数
		$tglist['grouplist']['title'] = '圈子';
		$tglist['grouplist']['data'] = M()->query("SELECT g.id AS id, g.name, g.logo, m.* FROM ts_group g LEFT JOIN (SELECT gid FROM ts_group_member WHERE {$map} STATUS = 1)  m ON m.gid=g.id 
                    WHERE g.is_del = 0 AND g.grouptype = 0 AND isrecom = 1 GROUP BY g.id ORDER BY m.gid");
		
		// 组织风采
		$tglist['hot_org']['title'] = '组织风采';
		$tglist['hot_org']['data']  = M()->query("SELECT u.uid,u.uname,wb.* FROM ts_user u LEFT JOIN (SELECT fid FROM ts_weibo_follow WHERE {$map} TYPE = 0)  wb ON wb.fid=u.uid 
                    WHERE deputyoriid IS NOT NULL AND u.uid<>1 AND u.isHot=1 GROUP BY uid ORDER BY fid");
        // $tglist['hot_org'] = array_slice($tglist['hot_org'], 0, 10);
		
		
		
		return $tglist;
	}
}