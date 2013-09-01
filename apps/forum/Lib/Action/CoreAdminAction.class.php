<?php

class CoreAdminAction extends Action{
	
	var $_Core = array();  //核心数据
	var $topic;  //主题表
	var $post ;  //回复表
	var $class;  //当前所在版块

	function _initialize(){

		$_GET	=	$this->_clean_data($_GET);
		$_POST	=	$this->_clean_data($_POST);

		$this->topic = D('Topic');
		$this->class = h( $_REQUEST['class'] );
		//$this->boardPurview(true);
		$this->assign( 'board', $this->class );
		//$this->getTopNav( $this->class );
		$this->site['title']	=	L('base_title');
		$this->setTitle();
	}

	//过滤值，不能有安全问题
	protected function _clean_data($data){
		if(is_array($data)){
			foreach($data as $k=>$v){
				if(is_array($v)){
					$data[$k]	=	$this->_clean_data($v);	
				}else{
					$data[$k]	=	h($v);
				}
			}
		}else{
			$data	=	h($data);
		}
		return $data;
	}
	
	function index(){
		$list['type'] = $_GET['type']?$_GET['type']:'index';
		if($list['type']=='recycle'){
			$map['isdel'] = 1;
		}else{
			$map['isdel'] = 0;
		}
		
		$classInfo = model('Xcate')->getTree('bbs',$this->class) ;
		$list['classInfo'] = $classInfo[0];
		$category = explode(',',$this->class);	
		
		/********* 只获取当前版块的主题 ********/
		$map['tagid'] = end($category);
		/********* 只获取当前版块的主题 ********/
		//精华贴
		if($_GET['dist']=='1'){
			$map['dist'] = 1;
		}
		
		//版块下专题分类
		$list['tclass']     = intval($_GET['tclass']);
		if($list['tclass']) $map['tclass'] = $list['tclass'];
		$list['tclasslist'] = D('Board')->gettClass( $this->class );
		
		//获取贴子列表，依照分类查找，还需要进行修改
		$list['topic']  = $this->topic->where($map)->field('*')->order('top DESC,rTime DESC')->findpage(20);
		$this->assign( $list );
		$this->display();
	}
	
	
	function movetclass(){
		$list['classid']    = intval($_REQUEST['classid']);
		$list['tclasslist'] = D('Board')->gettClass( $this->class );
		$this->assign( $list );
		$this->display();
	}
	
	function movecategory(){
		$cate = model('Xcate')->getTree('bbs','0');
		$json = json_encode($cate);
		$this->assign('class',$this->class);
		$this->assign('json',$json);
		$this->display();
	}
	
	function set(){
		$pBoard = D('Board');
		$map['tagid'] = end(explode(',',$this->class));
		if($_POST){
			$data['rule']        = h( $_POST['rule'] );
			if( $pBoard->where($map)->count() ){
				$pBoard->where($map)->data($data)->save();
			}else{
				$pBoard->add(array_merge( $map,$data ));
			}
			$this->success( L('do_success') );
		}else{
			$this->assign('boardInfo',$pBoard->where($map)->find());
			$this->display();
		}
	}
	
	
	
	function tclass(){
		$board = end(explode(',',$this->class));
		$list = m('bbs_tclass')->where('tagid='.$board)->order('ordernum ASC')->findall();
		foreach($list as $key=>$value){
			$list[$key]['count'] = D('Topic')->where("tagid=$board AND tclass=".$value['id'])->count();
		}
		$this->assign('list',$list);
		$this->assign( 'boardtag',$board );
		$this->display();
	}
	
	function dotclass(){
		switch ($_POST['type']){
			case 'saveCategory':
				D('Board')->savetClass( $this->class , $_POST['newaddname'] , $_POST['name'] ,$_POST['ordernum'] );
				redirect(U('bbs/Manage/tclass/',array('class'=> $this->class )));
			break;
			
			case 'deleteCategory':
				$id = intval($_POST['id']);
				$result =  M('bbs_tclass')->where("id=$id AND tagid='".end(explode(',',$this->class))."'")->delete();
				if($result){
					$this->success();
				}else{
					$this->error();
				}
			break;
		}
	}

	//管理操作
	function doadmin(){
		switch ($_POST['type']){			
			case 'deleteTopic':  //彻底删除主题
				$this->topic->dodelete( $_POST['id'] );
				D('Log')->put($this->mid,$this->class,L('delete').count($_POST['id']).L('log_do_topicnum'));
			break;
			
			case 'revertTopic':  //还原主题
				foreach ($_POST['id'] as $value){
					$this->topic->setField( 'isdel',0,'id='.$value );
				}
				D('Log')->put($this->mid,$this->class, L('revert').count($_POST['id']).L('log_do_topicnum'));
			break;
			
			case 'moveTorecycle': //将主题移入回收站
				
				foreach ($_POST['id'] as $value){
					$this->topic->setField( 'isdel',1,'id='.$value );
					echo $this->topic->getLastSql();
				}
				D('Log')->put($this->mid,$this->class, L('do_point').count($_POST['id']).L('log_do_movetotrecycle'));	
			break;
			
			case 'moveToTclass':  //转移专题
				$data['tclass'] = $_POST['newtclass'];
				$map['id']      = array( "IN",$_POST['id'] );
				$this->topic->setField('tclass',$data['tclass'],$map);
				D('Log')->put($this->mid,$this->class,L('do_point').count($_POST['id']).L('log_do_movetotclass'));	
			break;				
			
			case 'moveToCategory':  //转移分类
				$newBoard = $_POST['newboard'];
				$tid      = $_POST['id'];
				$this->topic->upBoard($tid,$newBoard);
				D('Log')->put($this->mid,$this->class, L('do_point').count($_POST['id']).L('log_do_movetotcategory'));	
			break;
		}
	}
	
	
	//日志记录
	function log(){
		$data = D('Log')->where('tagid='.end(explode(',',$this->class)))->order('cTime DESC')->findPage(20);
		$this->assign($data);
		$this->display();
	}
}
?>