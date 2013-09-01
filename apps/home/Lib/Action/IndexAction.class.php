<?php
define(APP_NUM,15);
class IndexAction extends Action{
	
	public function index() {
		if (service('Passport')->isLogged())
			redirect(U('home/User/index'));
		else
			$this->showIndex();
	}

	private function showIndex(){
                $type = ($_GET['type'] == 'org') ? 'org' : 'personal';
                $this->assign('type', $type);
                
                $apps = model('App')->where('is_recommend=1')->order('rec_time desc')->limit(9)->select();
                $this->assign('apps', $apps);
                
                $data['site_opt']  = model('Xdata')->lget('siteopt');
                
                
		unset($_SESSION['sina'], $_SESSION['key'], $_SESSION['douban'], $_SESSION['qq'],$_SESSION['open_platform_type']);

		//验证码
		$opt_verify = $this->_isVerifyOn('login');
		if ($opt_verify) {
			$this->assign('login_verify_on', $opt_verify);
		}

		$data['email'] = t($_REQUEST['email']);
		$data['uid']   = t($_REQUEST['uid']);
		$uids = array();

		// 正在热议 1小时缓存
		$data['hot_topic'] = D('Topic', 'weibo')->getHot();

		// 人气推荐old
		$data['hot_user']  = D('Follow', 'weibo')->getTopFollowerUser();
		$data['hot_user'] = array_slice($data['hot_user'], 0, 10);
		$uids = array_merge($uids, getSubByKey($data['hot_user'], 'uid'));
                
        //人气推荐new & 人气组织
        $data['top10']  = D('Follow', 'weibo')->getTopFollowerOrg($type);
		$data['top10'] = array_slice($data['top10'], 0, 10);
    	$uids = array_merge($uids, getSubByKey($data['top10'], 'uid'));
                
        //会员推荐
        $data['hot_member'] = S('_recommend_user_cache');
        $data['hot_member'] = array_slice($data['hot_member'], 0, 10);
		
		// 组织推荐
		$data['hot_org']  = S('_recommend_org_cache');
        $data['hot_org'] = array_slice($data['hot_org'], 0, 6);
		//$data['hot_group'] = array_slice($data['hot_user'], 0, 10);
		

		// 正在发生 (原创的文字微博)
		$data['lastest_weibo'] = D('Operate', 'weibo')->getLastWeibo();
		$data['lastest_weibo'] = array_slice($data['lastest_weibo'], 0, 12);
		$uids = array_merge($uids, getSubByKey($data['lastest_weibo'], 'uid'));
		$this->assign('since_id', empty($data['lastest_weibo']) ? 0 : $data['lastest_weibo'][0]['weibo_id'] );

		// 原创的图片微博
		$data['pic_weibo'] = S('S_login_pic_weibo');
		if(empty($data['pic_weibo'])) {
			$map['transpond_id'] = 0;
			$map['type']		 = 1;
			$map['isdel'] 		 = 0;
			$data['pic_weibo'] = D('Operate', 'weibo')->where($map)->order('weibo_id DESC')->limit(3)->findAll();
			S('S_login_pic_weibo', $data['pic_weibo'], 60);
		}

		$uids = array_merge($uids, getSubByKey($data['pic_weibo'], 'uid'));
		foreach ($data['pic_weibo'] as $k => $v){
			$imageData = unserialize($v['type_data']);
			if(isset($imageData[0])) {
				$data['pic_weibo'][$k]['type_data'] = $imageData[0];
			} else {
				$data['pic_weibo'][$k]['type_data'] = $imageData;
			}
		}

		D('User', 'home')->setUserObjectCache(array_flip(array_flip($uids)));
		
		$this->assign($data);
		$this->assign('regInfo',model('Xdata')->lget('register'));
		$this->display();
	}

	private function _isVerifyOn($type='login'){
		// 检查验证码
		if($type!='login' && $type!='register') return false;
		$opt_verify = $GLOBALS['ts']['site']['site_verify'];
		return in_array($type, $opt_verify);
	}

	/**  前台 应用管理  **/
	
	/*public function addapp() {
		$dao = model('App');
		$all_apps  = $dao->getOpenAppByPage();
		$installed = isset($_SESSION['installed_app_user_'.$this->mid]) ? $_SESSION['installed_app_user_'.$this->mid] :M('user_app')->where('`uid`='.$this->mid)->field('app_id')->findAll();
		$installed = getSubByKey($installed, 'app_id');
		$this->assign($all_apps);
		$this->assign('installed', $installed);
		$this->setTitle(L('add_apps'));
		$this->display();
	}*/
	/*
     * 新前台应用管理
     * 返回推荐应用与我的应用信息
     * @author jianjungki
     */
	public function addapp() {
        $dao = model('App');
        $installed = isset($_SESSION['installed_app_user_'.$this->mid]) ? $_SESSION['installed_app_user_'.$this->mid] :M('user_app')->where('`uid`='.$this->mid)->field('app_id')->findAll();
        $installed = getSubByKey($installed, 'app_id');
        
        foreach ($installed as $key=>$value) {
            if($key<count($installed)-1)
                $temp .= $value.",";
            else
                $temp .= $value;
        }
        if($temp)
            $str =' app_id in ('.$temp.') and show_status = 1 || ';
        //我的应用
        $listdata = $dao->where($str."status = 1 and show_status = 1 ")->order('display_order ASC')->limit(APP_NUM)->findAll();
        $this->assign('listdata', $listdata);
        //推荐应用
        $recomdata = $dao->where("is_recommend = 1 and show_status = 1")->order('display_order ASC')->limit(APP_NUM)->findAll();
        $this->assign('recomdata', $recomdata);
        //安装榜
        $installapp = $dao->where("status != 0 and show_status = 1")->order("add_num desc")->limit(10)->select();
        $this->assign('installapp',$installapp);
        //好评榜
        $bestapp = $dao->where("status != 0 and show_status = 1")->order("avgstar desc")->limit(10)->select();
        $this->assign('bestapp',$bestapp);
        //应用类型数据
        $app_type = D("Datadict")->where("pid = 6")->findAll();
        $this->assign("app_type",$app_type);
        
        $this->assign('installed', $installed);
        
        $this->setTitle(L('add_apps'));
        $this->display();
    }
	public function editapp() {
		// 重置用户的漫游应用的缓存
		global $ts;
		if ($ts['site']['my_status'])
			model('Myop')->unsetAllInstalledByUser($this->mid);
		
		$this->assign('has_order', array('local_app', 'myop_app'));
		$this->setTitle(L('manage_apps'));
		$this->display();
	}
	
	public function install() {
		$app = isset($_GET['app_name']) ? 
			   model('App')->getAppDetailByName(t($_GET['app_name'])) :
			   model('App')->getAppDetailById(intval($_GET['app_id']));
		if (!$app || $app['status'] == 0)
			$this->error(L('app_notexist'));
	    
        
		$this->assign($app);
		$this->setTitle(''.L('install').'"' . $app['app_alias'] . '"'.L('app'));
		$this->display();
	}
	
	public function doInstall() {
		$_GET['app_id'] = intval($_GET['app_id']);
		$app = model('App')->getAppDetailById($_GET['app_id']);
		if (!app || $app['status'] == 0)
			$this->error(L(app_notexist));
			
		if (model('App')->addAppForUser($this->mid, $_GET['app_id'])) {
			model('App')->unsetUserInstalledApp($this->mid);
            //安装数量
            $num =  M('UserApp')->where("app_id = ".$_GET['app_id'])->count();
            D("App")->where('app_id = '.$_GET['app_id'])->setField("add_num",$num);
            //安装数量end
			$this->assign('jumpUrl', U($app['app_name'].'/'.$app['app_entry'],array("appid"=>$_GET['app_id'])));
			$this->success(L('install_success'));
		} else {
			$this->error(L('install_error'));
		}
	}
	
	public function uninstall() {
		$_GET['app_id'] = intval($_GET['app_id']);
		if (model('App')->where('`app_id`='.$_GET['app_id'])->getField('status') == '1')
			$this->error(L('default_app'));
		
		if (model('App')->removeAppForUser($this->mid, $_GET['app_id'])) {
			model('App')->unsetUserInstalledApp($this->mid);
			$this->assign('jumpUrl', U('home/Index/editapp'));
			$this->success(L('uninstall_success'));
		} else {
			$this->error(L('uninstall_error'));
		}
	}
	
	public function doOrder() {
		global $ts;
		$has_order  = array('local_app', 'myop_app');
		$table_name = array('local_app'=>'user_app', 'myop_app'=>'myop_userapp');
		$order_field_name = array('local_app'=>'display_order', 'myop_app'=>'displayorder');
		$app_id_name	  = array('local_app'=>'app_id', 'myop_app'=>'appid');
		
		// 现在的顺序 array('app_id'=>'order')
		$now_order = array();
		foreach ($has_order as $v)
			foreach ($ts['user_app'][$v] as $app)
				$now_order[$v][$app['app_id']] = $app['display_order'];
		
		$has_changed = false;
		foreach ($_POST as $field => $v) {
			if ( !in_array($field, $has_order) )
				continue ;
			foreach ($v as $order => $app_id) {
				$order  = intval($order);
				$app_id = intval($app_id);

				// 只更新有变化的顺序号
				if ($order == $now_order[$field][$app_id])
					continue ;
				// 提交修改
				if ( M($table_name[$field])->where("`{$app_id_name[$field]}`='$app_id' AND `uid`='{$this->mid}'")->setField($order_field_name[$field], $order) )
					$has_changed = true;
				else
					$this->error(L('save_error'));
			}
		}
		
		// 重置缓存
		model('App')->unsetUserInstalledApp($this->mid);
		global $ts;
		if ($ts['site']['my_status'])
			model('Myop')->unsetAllInstalledByUser($this->mid);
		
 		if ($has_changed)
			$this->success(L('save_success'));
		else
			$this->error(L('order_nochange'));
	}
    
    /*
     * @author:jianjungki
     * @time: 2012.10.22
     * @用于搜索app
     */
     function doSearchApp(){
         $apptype = $_REQUEST['app_type'];
         $apptypeid = $_REQUEST['app_type_id'];
         $appname = $_REQUEST['app_name'];
         $this->assign("checkname",$appname);
         $this->assign("checkid",$apptypeid);
         
        $installed = isset($_SESSION['installed_app_user_'.$this->mid]) ? $_SESSION['installed_app_user_'.$this->mid] :M('user_app')->where('`uid`='.$this->mid)->field('app_id')->findAll();
        $installed = getSubByKey($installed, 'app_id');
        
        foreach ($installed as $key=>$value) {
            if($key<count($installed)-1)
                $temp .= $value.",";
            else
                $temp .= $value;
        }
        $this->assign('installed', $installed);
         //应用类型数据
         $app_type = D("Datadict")->where("pid = 6")->findAll();
         $this->assign("app_type",$app_type);
         
         $dao = model('App');
         //安装榜
        $installapp = $dao->where("status != 0 and show_status = 1")->order("add_num desc")->limit(10)->select();
        $this->assign('installapp',$installapp);
        //好评榜
        $bestapp = $dao->where("status != 0 and show_status = 1")->order("avgstar desc")->limit(10)->select();
        $this->assign('bestapp',$bestapp);
         if($apptypeid==0){
             $search_data = D('App')->AppSortByInstalled($this->mid,"app_alias like '%".$appname."%'");
         }else if(!empty($appname)){
             $search_data = D('App')->AppSortByInstalled($this->mid,"app_alias like '%".$appname."%' AND category = '".$apptype."'");
         }else {
             $search_data = D('App')->AppSortByInstalled($this->mid," category = '".$apptype."'");
         }
         $this->assign("search_data",$search_data);
         $this->display("addapp");
     }
     
     function NextMyApp(){
         $getnum = $_POST['appnum'];
         if($getnum>=1)
            $fromapp = APP_NUM*$getnum;
         else $fromapp=0;
         //$toapp=5;
         //$fromapp =0;
         $dao = model('App');
        $installed = isset($_SESSION['installed_app_user_'.$this->mid]) ? $_SESSION['installed_app_user_'.$this->mid] :M('user_app')->where('`uid`='.$this->mid)->field('app_id')->findAll();
        $installed = getSubByKey($installed, 'app_id');
        
        foreach ($installed as $key=>$value) {
            if($key<count($installed)-1)
                $temp .= $value.",";
            else
                $temp .= $value;
        }
         //我的应用
        $listdata = $dao->where("(app_id in (".$temp.") OR status = 1) and show_status = 1")->order('display_order ASC')->limit("$fromapp,".APP_NUM)->select();
        if(!$listdata){
            echo 0;return;
        }
        foreach ($listdata as $key => $value) {
             $res_content .= "<li><div>";
             if($value['host_type']!=0)
                $res_content .= '<img src="'.$value['large_icon_url'].'" class="userPhoto" width="64px" height="64px"></div>';
             else
                 $res_content .='<img src="'.getAppIconUrl($value['large_icon_url'],$value['app_name']).'" class="userPhoto" width="64px" height="64px"></div>';
             $res_content .= '<h4 style="width:120px;"><a href="'.U('onlineapp/Index/index',array('appid'=>$value['app_id'])).'">'.$value['app_alias'].'</a></h4><br>';
             $res_content .= '<div style="width:120px;"><a href="javascript:ui.commentit('.$value['app_id'].');">应用评价</a>|&nbsp;<span>已添加</span></div></li>';
        }
        //推荐应用
        
        echo $res_content;
     }

     function NextRecomApp(){
         $getnum = $_POST['appnum'];
         if($getnum>=1)
            $fromapp = APP_NUM*$getnum;
         else $fromapp=0;
         $dao = model('App');
         //推荐应用
         $recomdata = $dao->where("is_recommend = 1  and show_status = 1")->order('display_order ASC')->limit("$fromapp,".APP_NUM)->select();
         if(!$recomdata){
            echo 0;return;
        }
         $installed = isset($_SESSION['installed_app_user_'.$this->mid]) ? $_SESSION['installed_app_user_'.$this->mid] :M('user_app')->where('`uid`='.$this->mid)->field('app_id')->findAll();
        $installed = getSubByKey($installed, 'app_id');
         foreach ($recomdata as $key => $value) {
             $res_content .= "<li><div>";
             if($value['host_type']!=0)
                $res_content .= '<img src="'.$value['large_icon_url'].'" class="userPhoto" width="64px" height="64px"></div>';
             else
                 $res_content .='<img src="'.getAppIconUrl($value['large_icon_url'],$value['app_name']).'" class="userPhoto" width="64px" height="64px"></div>';
             $res_content .= '<h4 style="width:120px;"><a href="'.U('onlineapp/Index/index',array('appid'=>$value['app_id'])).'">'.$value['app_alias'].'</a></h4><br>';
             $res_content .= '<div style="width:120px;">';
             if(in_array($value['app_id'],$installed) || $value['status']==1)
                $res_content .= '<span>已添加</span></div></li>';
             else
                 $res_content .= '<a href="'.U('home/Index/install',array('app_id'=>$value['app_id'])).'">我要添加</a></div></li>';
         
         }
         echo $res_content;
     }
   
}
