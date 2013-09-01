<?php
class ScheduleModel extends Model{
	public function todayTopic(){
		//计算今天时间
		$today =  mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		//每半小时执行一次
		$map['cTime'] = array('between',array($today,time()));
		$map['isdel'] = 0;
		//统计所有主题
		$topicCount = $this->table(C('DB_PREFIX')."forum_topic")->
						field("count(tid) as topicCount,fid")->
						group('fid')->where($map)->findAll();
		foreach($topicCount as $key=>$value){
			$m['fid'] = $value['fid'];
			$data['today_thread_count'] = $value['topicCount'];
			M('forum')->where($m)->save($data);
		}
		//统计所有版块的主题	
	}
	
	public function deleteTopic(){
		//定时删帖
		//扫描每个团队的设置
		//判断是否定时删除帖
		//通过设置的时间差确定delete的where条件
		//注意。假删除
		$forumTopic = D('ForumTopic');
		$forumSetting = D('ForumSetting');
		$map['del_by_time'] = array('gt',0);
		$forum = $forumSetting->where($map)->findAll();
		$data['fid']= array('in',getSubByKey($forum,'fid'));
		M('forum_post')->where($data)->setField('isdel',1);
		foreach($forum as $key=>$value){
			$m['cTime'] = array('lt',time()-$value['del_by_time']);
			$forumTopic->where($m)->setField('isdel',1);
		}
	}
	
	public function todayPost(){
		//每半15分钟执行一次
		$today =  mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		$map['cTime'] = array('between',array(time()-$today,time()));
		//统计所有回帖
		$map['isdel'] = 0;
		$map['istopic'] = 0;
		$postCount = $this->table(C('DB_PREFIX')."forum_post")->
						field("count(pid) as postCount,fid")->
						group('fid')->where($map)->findAll();
		//统计所有版块的回帖
		foreach($postCount as $key=>$value){
			$map['fid'] = $value['fid'];
			$data['today_post_count'] = $value['postCount'];
			M('forum')->where($map)->save($data);
		}
		
	}
	
	public function todayOnline(){
		//每1小时执行一次
		//统计所有在线人数
		//统计所有版块的在线人数	
	}
	
	public function todayView(){
		//每半小时执行一次
		$today =  mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		$map['cTime'] = array('between',array(time()-$today,time()));
		$map['isdel'] = 0;
		$postCount = $this->table(C('DB_PREFIX')."forum_post")->
						field("sum(pid) as viewCount,fid")->
						group('fid')->where($map)->findAll();
		//统计每个今日版块帖子的View值
		foreach($postCount as $key=>$value){
			$map['fid'] = $value['fid'];
			$data['today_post_count'] = $value['postCount'];
			M('forum')->where($map)->save($data);
		}
	}
	
	public function topicCount(){
		//每15分钟执行一次
		//每个版块主题数
	}
	public function viewCount(){
		//每15分钟执行一次
		//每个版块的主题View值统计
	}
	
	public function postCount(){
		//每15分钟执行一次
		//每个版块的主题回复数统计
	}
}


?>