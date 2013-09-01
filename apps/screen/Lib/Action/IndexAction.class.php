<?php
class IndexAction extends Action
{
	private static $buffer = array(ScreenModel::TYPE_OLD=>'<em class="ico_dpm_end">已结束</em>',
		               ScreenModel::TYPE_RUNING=>'<em class="ico_dpm_ing">正在进行</em>',
		               ScreenModel::TYPE_WILL_START=>'<em class="ico_dpm_begin">即将开始</em>');
	private static $color = array(ScreenModel::TYPE_OLD=>'',
		               ScreenModel::TYPE_RUNING=>'time_ing',
		               ScreenModel::TYPE_WILL_START=>'time_begin');
	private static $tabArray = array(ScreenModel::TYPE_ALL=>'全部案例',
	                   ScreenModel::TYPE_RUNING=>'正在进行',
	                   ScreenModel::TYPE_WILL_START=>'尚未开始',
	                   ScreenModel::TYPE_OLD=>'历史回顾',
        	           );
	public function _initialize()
	{
		// 验证是否允许匿名访问微博广场
		if ($this->mid <= 0 && intval(model('Xdata')->get('siteopt:site_anonymous_square')) <= 0) {
			redirect(U('home'));
		}

        $expend_menu = array();
        Addons::hook('home_square_tab', array('menu' => & $expend_menu));
        $this->assign('expend_menu', $expend_menu);
	}

	public function index()
	{
		$model = D('Screen');
		$list  =  $model->getScreenList(intval($_GET['type']));


	    $tab = array();
	    foreach(self::$tabArray as $key=>$value){
	        $typeClass=null;
	        if(intval($_GET['type']) == $key || (empty($_GET['type']) && $key == ScreenModel::TYPE_ALL)) $typeClass='on';
	        $tab[] = sprintf('<a href="%s" class="feed_item %s"><span>%s</span></a>',U('screen/index/index',array('type'=>$key)),$typeClass,$value);
	    }
        $list['data'] = $this->_getDetailOtherDataForMore($list['data'],$_GET['type']);


		$count = $model->getScreenCount();

		$this->assign('tab',$tab);
		$this->assign('list',$list);
		$this->assign('count',$count);
		$this->display();
	}

	public function apply()
	{
	    $this->display();
	}

	public function getScreenWeiboIsShow(){
	    $weibo_id = $_POST['weibo_id'];
	    $screen_id = intval($_POST['screen_id']);
    	    foreach($weibo_id as $key=>$value){
    	        $weibo_id[$key] = intval($value);
    	    }
    	    $weibo_id = array_filter($weibo_id);
    	    try{
    	        $res = D('ScreenWeibo')->getShowedWeiboId($screen_id,$weibo_id,$this->mid);
    	        $this->ajaxData['status'] = $_SESSION['screen_'.$screen_id]=="start"?"start":"false";
    	        $this->display('show');
    	        $this->success($res);
    	    }catch(ThinkException $e){
    	        $this->error($e->getMessage());
    	    }
	}

	public function show(){
        try{
            $screen_data = $this->_getDetailOtherDataForOne(D('Screen')->getScreen(intval($_GET['id'])));
            $this->assign('screen',$screen_data);
            $this->display();
        }catch (ThinkException $e){
            $this->error($e->getMessage());
        }
	}


	public function showWeibo(){
	    $count = 10;
	    if($_SESSION['screen_'.$_REQUEST['screen_id']] == "start"){

    	    try{
    	        $queue = D('ScreenWeibo')->getShowWeiboQueue(intval($_REQUEST['screen_id']),$this->mid,intval($_REQUEST['last']),$count);
    	        $this->ajaxData=$queue;
    	        $last = $queue[count($queue)-1]['screenTime'];
    	        $this->success($last);
    	    }catch(ThinkException $e){
    	        $this->error($e->getMessage());
    	    }
	    }
	    $this->error('暂停了');
	}


	public function detail()
	{
		$k = t($_GET['k']);
		$this->assign('k',$k);
	    try{
	        $screen_data = $this->_getDetailOtherDataForOne(D('Screen')->getScreen(intval($_GET['id'])));
            $recommend_screen = D('Screen')->getRcommend();
            foreach ($recommend_screen as $v) {
            	$id[] = $v['logo'];
            }
            $i = 0;
    		foreach ($recommend_screen as &$v) {
    			$map['id'] = array('in',$v['logo']);
                $attach = M('attach')->where($map)->find();
                $src = $attach['savepath'].$attach['savename'];
                $v['src'] = $src;
    			$i++;

    		}
    		
            $key = implode(" OR ", $search);
    	    $this->assign('screen',$screen_data);
    	    $this->assign('recommend',$recommend_screen);
    	    if($screen_data['uid'] == $this->mid && $screen_data['type'] != ScreenModel::TYPE_OLD){
    	        $this->_admin($screen_data);
    	    }else{
    	        $list = $this->_searchData($screen_data);
    	        $this->assign('list',$list);
    	        $this->display();
    	    }
	    }catch(ThinkException $e){
	        $this->error($e->getMessage());
	    }
	}

	private function _admin($screen_data){
	    $detail_tab_array = array("所有讨论","搜索微博");
	    $detail_tab = array();
	    foreach($detail_tab_array as $key=>$value){
	        if($_GET['type'] == $key){
	            $detail_tab[$key]['class'] = 'on';
	        }else{
	            $detail_tab[$key]['class'] = 'off';
	        }
	        $detail_tab[$key]['url'] = U('screen/index/detail',array('type'=>$key,'id'=>$screen_data['screen_id']));
	        $detail_tab[$key]['name'] = $value;
	    }

	    $queue = D('ScreenWeibo')->getShowWeiboQueue($screen_data['screen_id'],$this->mid);
	    $list = $this->_searchData($screen_data,$_GET['k']);
	    $manage = $_SESSION['screen_'.$screen_data['screen_id']] == "start"?"btn_pause":"btn_play";
	    $this->assign('manage',$manage);
	    $this->assign('queue',$queue);
	    $this->assign('list',$list);
	    $this->assign('tab',$detail_tab);
	    $this->display('admin');
	}



	private function _searchData($screen_data,$searchKey = null){
	    if($screen_data['uid'] == $this->mid && !empty($searchKey)){
	        $keyword = explode(' ',$searchKey);
	    }else{
	        $searchKey = $screen_data['keyword'];
	        $keyword = explode(';',$searchKey);
	        $keyword[] = '#'.$screen_data['topic'].'#';
	    }
	    $keyword = array_filter($keyword);

	    $search  = array();
	    foreach($keyword as $value){
	        $value = trim($value);
	        $search[] = "content like '%{$value}%'";
	    }
	    $key = implode(" OR ", $search);
	    $data = D ( 'Operate', 'weibo' )->doSearch ( $key, "keyword");
	    return $data;
	}

	private function _getDetailOtherDataForMore($data,$type=ScreenModel::TYPE_ALL){
	    $logo = array();
	    $temp = array();
	    foreach($data as $key=>$v){
	        if($type == ScreenModel::TYPE_ALL){
	            $data[$key]['class']['buffer'] = self::$buffer[$v['type']];
	            $data[$key]['class']['color']  = self::$color[$v['type']];
	        }
	        $data[$key]['time_start']      = friendlyDate($v['time_start'],'ymd');
	        $data[$key]['time_end']        = friendlyDate($v['time_end'],'ymd');
	        if(!empty($v['logo'])){
	            $logo[$v['screen_id']] = $v['logo'];
	            $temp[$v['logo']] = $key;
	        }else{
	            $data[$key]['logo'] = __THEME__.'/images/ts.png';
	        }
	    }
	    $logo = model('Attach')->getAllAttachById($logo);
	    foreach($temp as $key=>$value){
	        $data[$value]['logo'] = $logo[$key];
	    }
	    return $data;
	}

	private function _getDetailOtherDataForOne($data){
	    $data['class']['buffer'] = self::$buffer[$data['type']];
	    $data['class']['color']  = self::$color[$data['type']];
	    $data['time_start']      = friendlyDate($data['time_start'],'ymd');
	    $data['time_end']        = friendlyDate($data['time_end'],'ymd');
	    if(!empty($data['logo'])){
	        $data['logo']= model('Attach')->getAllAttachById($data['logo']);
	    }else{
	        $data['logo'] = __THEME__.'/images/ts.png';
	    }
	    return $data;
	}


}