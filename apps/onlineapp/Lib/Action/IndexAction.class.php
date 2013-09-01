<?php
class IndexAction extends Action{
    
    public function index() {
        $info = D("App")->where('app_id = '.$_GET['appid'])->find();
        $this->assign($info);
        $installed = isset($_SESSION['installed_app_user_'.$this->mid]) ? $_SESSION['installed_app_user_'.$this->mid] :M('user_app')->where('`uid`='.$this->mid)->field('app_id')->findAll();
        $installed = getSubByKey($installed, 'app_id');
        $this->assign('installed', $installed);
        //获取相关应用
        $like_app = D("App")->LikeAppGet($info['app_id'],$info['category']);
        $this->assign("applike",$like_app);
        //在线应用
        if((in_array($info['app_id'],$installed)||$info['status']==1)&&$_GET['is_show']&&$info['host_type']==1){
            redirect(U('onlineapp/Index/showapp'.'&appid='.$_GET['appid']));
        }
        $messagepage = D("AppComment")->where("appid = ".$_GET['appid'])->order("id desc")->findPage(6);
        $this->assign($messagepage);
        
        //本地应用
        if (service('Passport')->isLogged())
            $this->display();
        else
            redirect(U('home/User/index'));
    }
    /*
     * 提交评价信息，好评还是差评，增加评论信息
     * @author jianjungki
     * @time 2012-09-19
     */
   public function addInfo() {
        $Eva =  M('Evaluate');
        //获取评价
        $apptem = $Eva->where("uid = {$this->mid} and appid = ".$_POST['appid'])->find();
        
        $Eva->startTrans();
        //事务开始
        if($apptem['putinfo']==0&&$_POST['putinfo']==1){
            $_POST['positivenum']--;$_POST['negativenum']++;
            $Eva->where("uid = {$this->mid} and appid = ".$_POST['appid'])->setField(array('commentinfo','putinfo'),array($_POST['coomment'],$_POST['putinfo']));
        }else if($apptem['putinfo']==1&&$_POST['putinfo']==0){
            $_POST['positivenum']++;$_POST['negativenum']--;
            $Eva->where("uid = {$this->mid} and appid = ".$_POST['appid'])->setField(array('commentinfo','putinfo'),array($_POST['coomment'],$_POST['putinfo']));
        }
        $data['appid'] = $_POST['appid'];
        $data['uid'] = $this->mid;
        //如果评论为空则不加入
        if(!empty($_POST['coomment'])){
            $data['commentinfo'] = $_POST['coomment'];
            M("AppComment")->data($data)->add();
        }
        if(!$apptem){
            $data['putinfo'] = $_POST['putinfo'];
            $Eva->data($data)->add();
        }
        
        
        if($_POST['positivenum']<0)$_POST['positivenum']=0;
        if($_POST['negativenum']<0)$_POST['negativenum']=0;
        
        //判断是否已经设置评价
        if($apptem['putinfo']!=$_POST['putinfo'])
            $info = D("App")->where('app_id = '.$_POST['appid'])->setField(array('negativenum','positivenum','add_num'),array($_POST['negativenum'],$_POST['positivenum'],$num));
        else
            $info = 1; 
        //dump($apptem);
        //dump($_POST['negativenum']);
        //dump($_POST['positivenum']);
        //事务结束
        $Eva->commit();
        if($info)
            $this->success(L("Success"));
        else
            $this->error(L("Failure"));
    }
   /*
     * 下载客户端action，待用
     * @author jianjungki
     * @time 2012-09-19
     */
   public function downLoad() {
        
    }
    /*
     * 在线应用action，显示在线应用页面
     * @author jianjungki
     * @time 2012-09-21
     */
   public function showapp(){
   		if($_GET['app_alias']){
   			//根据id查找应用
   			$map['app_alias'] = $_GET['app_alias'];
   		}else{
   			//根据应用别名查找应用
   			$map['app_id'] = $_GET['appid'];
   		}
		
       $info = D("App")->where($map)->find();
	   
       if(!empty($info['link_url'])){
       	  if($info['app_alias'] == 'TOA')
       	  {
       	  	$this->assign('urllink',U('home/Public/singLoginOA'));
       	  	$this->assign('name',$info['app_alias']);
       	  }else{
       	  		//判断是否带有传入的参数
			   if ($_GET['param']) {
				   $info['link_url'] = $info['link_url'] . $_GET['param'];
			   }
       	  	$this->assign('urllink',$info['link_url']);
       	  	$this->assign('name',$info['app_alias']);
       	  }
       }else{
           $this->error("该应用不是在线应用");
       }
       $this->display();
   }
   
   /*
     * 添加评价信息，对应用进行反馈
     * @author jianjungki
     * @time 2012-09-19
     */
   public function addComment() {
        $data['appid'] = 2;
        $data['uid'] = 1;
        $data['putinfo'] = 1;
        $data['commentinfo'] = 1;
        $info = M("Evaluate")->data($data)->add();
        //dump($info);
    }
}
