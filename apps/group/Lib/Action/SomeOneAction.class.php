<?php
//某人的圈子
class SomeOneAction extends BaseAction {

	public function _initialize() {
		parent::base();

		/*
		 * 右侧信息
		 */
    	// 加入的圈子的数量
    	$join_group_count = D('Member')->where('(deputyorgid is not NULL AND level>1 AND status=1)  AND uid=' . $this->mid)->count();
    	$this->assign('join_group_count', $join_group_count);

    	// 热门标签
    	$hot_tags_list = D('GroupTag')->getHotTags();
    	$this->assign('hot_tags_list', $hot_tags_list);

    	// 圈子热门排行
    	$hot_group_list = D('Group')->getHotList();
    	$this->assign('hot_group_list', $hot_group_list);
	}

	public function index()
	{
		if ($_GET['grouptype'] ) {
			$this->grouptype= $_GET['grouptype'];
		}
		$type = in_array($_GET['type'], array('join', 'manage', 'following')) ? $_GET['type'] : 'all';
		switch ($type) {
			case 'join': // 我管理的
				$group_list = D('Group')->myjoingroup($this->uid, 1);
        		$this->setTitle("我加入的圈子");
				break;
			case 'manage': // 我加入的
				$group_list = D('Group')->mymanagegroup($this->uid, 1);
        		$this->setTitle("我管理的圈子");
				break;
			case 'following': // 我关注的人的
				$db_prefix  = C('DB_PREFIX');
				$group_list = D('Group')->Distinct(true)->field('g.id,g.uid,g.name,g.type,g.membercount,g.logo,g.cid0,g.ctime,g.status')
    							->table("{$db_prefix}group AS g LEFT JOIN {$db_prefix}weibo_follow AS f ON f.uid={$this->uid} AND g.uid=f.fid")
    							->where(' grouptype = 0 AND g.status=1 AND g.is_del=0 AND f.fid<>\'\'')
    							->findPage();
        		$this->setTitle("我关注的人的圈子");
				break;
			default:
				$group_list = D('Group')->getAllMyGroup($this->uid, 1);
        		$this->setTitle("我的圈子");
		}
		//圈子成员  & 重新统计成员数
		foreach ($group_list['data'] as $key => $value) {
			$memberId = D('Group','group')->getMemberId($value['id'],8);
			$membercount = D('Group','group')->getMemberId($value['id']);
			$membercount = count($membercount);
			$group_list['data'][$key]['member'] = $memberId;
			$group_list['data'][$key]['membercount'] = $membercount;
		}
		$this->assign('current_uname', ($this->mid == $this->uid) ? '我' : getUserName($this->uid));
		$this->assign('type', $type);
		$this->assign('grouplist', $group_list);
		$this->display();
	}

	//话题
	function topic(){
		//加入的
		$type = isset($_GET['type']) ? $_GET['type'] : '';
		$cond = '';
		if($type == 'post'){  //发表
			$cond = ' AND istopic=1 ';

		}elseif($type == 'reply'){  //回复
			$cond = ' AND istopic=0 ';
		}
		$postList = D('Post')->where('uid='.$this->uid." $cond AND is_del=0")->order('ctime DESC')->findPage();
		foreach($postList['data'] as $k=>$v){
			$postList['data'][$k] = gettopic($v['tid']);
		}
		$this->assign('type',$type);
		$this->assign('topicList',$postList);
		$this->display();
	}
	
	
	/*
      * 组织
      * @author: lianggh
      */
	
	public function projectIndex()
	{
		if ($_GET['grouptype'] ) {
			$this->grouptype= $_GET['grouptype'];
		}
		$hot_group_list=null;
		$hot_group_list = D('Group')->getHotList(true,true);
    	$this->assign('hot_group_list', $hot_group_list);
			
		$type = in_array($_GET['type'], array('join', 'manage', 'following','done')) ? $_GET['type'] : 'all';
		switch ($type) {
			case 'join': // 我管理的
				$group_list = D('Group')->myjoingroup($this->uid, 1,1);
        		$this->setTitle("我加入的项目");
				break;
			case 'manage': // 我加入的
				$group_list = D('Group')->mymanagegroup($this->uid, 1,1);
        		$this->setTitle("我管理的项目");
				break;
			case 'following': // 我关注的人的
				$db_prefix  = C('DB_PREFIX');
				if ((ACTION_NAME=='projectIndex')||isProject($_GET['gid'])) {
				$pro = 'deputyorgid is not NULL AND ';
				}else{
					$pro = 'deputyorgid is NULL AND ';
				}
				$group_list = D('Group')->Distinct(true)->field('g.id,g.name,g.type,g.membercount,g.logo,g.cid0,g.ctime,g.status')
    							->table("{$db_prefix}group AS g LEFT JOIN {$db_prefix}weibo_follow AS f ON f.uid={$this->uid} AND g.uid=f.fid")
    							->where($pro . ' g.status=1 AND g.is_del=0 AND f.fid<>\'\'')
    							->findPage();
        		$this->setTitle("我关注的人的项目");
				break;
			case 'done': // 已的完成项目
				$map['isDone'] = array('EQ','1');
				$map['uid'] = array('EQ',$this->mid);
				$order = array('ctime'=>'desc');
				$group_list = D('Group')->getProjectByMap($map, '*', 5, $order,true);
				// dump($group_list);
				break;	
			default:
				$group_list = D('Group')->getAllMyGroup($this->mid,1,array(),6,1);
        		$this->setTitle("我的项目");
		}
		foreach ($group_list['data'] as $key => $value) {
			$memberId = D('Group','group')->getMemberId($value['id'],8);
			$group_list['data'][$key]['member'] = $memberId;
		}
		
		$this->assign('current_uname', ($this->mid == $this->uid) ? '我' : getUserName($this->uid));
		$this->assign('type', $type);
		$this->assign('grouplist', $group_list);
		$this->display();
	}
	
	/*
      * 组织
      * @time: 2012.10.19
      * @author: lianggh
      */
	
	public function orgIndex()
	{
		if ($_GET['grouptype'] ) {
			$this->grouptype= $_GET['grouptype'];
		}
		$hot_group_list=null;
		$hot_group_list = D('Group')->getHotList(true,true);
    	$this->assign('hot_group_list', $hot_group_list);
			
		$type = in_array($_GET['type'], array('join', 'manage', 'following','done')) ? $_GET['type'] : 'all';
		switch ($type) {
			case 'join': // 我管理的
				$group_list = D('Group')->myjoingroup($this->uid, 1,2);
        		$this->setTitle("我加入的组织");
				break;
			case 'manage': // 我加入的
				$group_list = D('Group')->mymanagegroup($this->uid, 1,2);
        		$this->setTitle("我管理的组织");
				break;
			case 'following': // 我关注的人的
				$db_prefix  = C('DB_PREFIX');
				if ((ACTION_NAME=='projectIndex')||isProject($_GET['gid'])) {
				$pro = 'deputyorgid is not NULL AND ';
				}else{
					$pro = 'deputyorgid is NULL AND ';
				}
				$group_list = D('Group')->Distinct(true)->field('g.id,g.name,g.type,g.membercount,g.logo,g.cid0,g.ctime,g.status')
    							->table("{$db_prefix}group AS g LEFT JOIN {$db_prefix}weibo_follow AS f ON f.uid={$this->uid} AND g.uid=f.fid")
    							->where($pro . ' g.status=1 AND g.is_del=0 AND f.fid<>\'\'')
    							->findPage();
        		$this->setTitle("我关注的人的组织");
				break;
			case 'done': // 已的完成组织
				$map['isDone'] = array('EQ','1');
				$map['uid'] = array('EQ',$this->mid);
				$order = array('ctime'=>'desc');
				$group_list = D('Group')->getProjectByMap($map, '*', 5, $order,true);
				// dump($group_list);
				break;	
			default:
				$group_list = D('Group')->getAllMyGroup($this->mid,1,array(),6,2);
        		$this->setTitle("我的组织");
		}
		
		foreach ($group_list['data'] as $key => $value) {
			$memberId = D('Group','group')->getMemberId($value['id'],8);
			$group_list['data'][$key]['member'] = $memberId;
		}
		
		$this->assign('current_uname', ($this->mid == $this->uid) ? '我' : getUserName($this->uid));
		$this->assign('type', $type);
		$this->assign('grouplist', $group_list);
		$this->display();
	}

	
	
}