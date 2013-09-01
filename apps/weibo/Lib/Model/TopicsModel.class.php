<?php 
class TopicsModel extends Model{
	var $tableName = 'weibo_topics';

	// 专题列表
	public function topicsList($post = array())
	{
        $post['topics_id'] && $map['topics.topics_id'] = array('in', t($post['topics_id']));
        $post['name']      && $map['topic.name'] 	    = array('like', '%' . t($post['name']) . '%');
        $post['content']   && $map['topics.content']   = array('like', '%' . t($post['content']) . '%');
		is_string($post['recommend']) && $map['topics.recommend'] = (string)intval($post['recommend']);
		$map['topics.isdel'] = 0;
		$map['topics.isvisit'] = 0;
        //$order = ( $post['orderkey'] && $post['ordertype'] ) ? $post['orderkey'] . ' ' . $post['ordertype']:'weibo_id DESC';
        $order = 'topics.recommend ASC,topics.topics_id DESC';

		$list = $this->field('topics.*,topic.name')
					 ->table("{$this->tablePrefix}weibo_topics as topics
					 		  LEFT JOIN {$this->tablePrefix}weibo_topic as topic
					 		  ON topics.topic_id=topic.topic_id")
					 ->where($map)->order($order)->findPage(20);
		return $list;
	}
	// 微访谈专题列表
	public function topicsviewList($post = array())
	{
		$post['topics_id'] && $map['topics.topics_id'] = array('in', t($post['topics_id']));
		$post['name']      && $map['topic.name'] 	    = array('like', '%' . t($post['name']) . '%');
		$post['content']   && $map['topics.content']   = array('like', '%' . t($post['content']) . '%');
		is_string($post['recommend']) && $map['topics.recommend'] = (string)intval($post['recommend']);
		$map['topics.isdel'] = 0;
        $map['topics.isvisit'] = 1;
		$order = 'topics.recommend ASC,topics.topics_id DESC';
	
		$list = $this->field('topics.*,topic.name')
		->table("{$this->tablePrefix}weibo_topics as topics
		LEFT JOIN {$this->tablePrefix}weibo_topic as topic
		ON topics.topic_id=topic.topic_id
		
		")
		->where($map)->order($order)->findPage(20);
		return $list;
	}

	// 获取推荐专题列表
	public function getHot()
	{
		$list = $this->field('topic.name,topic.count,topics.domain,topics.note')
					 ->table("{$this->tablePrefix}weibo_topics as topics
					 		  LEFT JOIN {$this->tablePrefix}weibo_topic as topic
					 		  ON topics.topic_id=topic.topic_id")
					 ->where('topics.recommend=1 AND topics.isdel=0')
					 ->order('topics.topics_id DESC')->findAll();
		return $list;
	}
	
	//获取微访谈话题列表
	public function getTopicList()
	{
	  
	 	$list = $this->field('topic.name,topics.status')
						 ->table("{$this->tablePrefix}weibo_topics as topics
						 		  LEFT JOIN {$this->tablePrefix}weibo_topic as topic
						 		  ON topics.topic_id=topic.topic_id")
						 ->where(' topics.isdel=0 AND topics.isvisit=1 ')
						 ->limit(2)
						 ->order('topics.time_start DESC')->select(); 
		
		 /*$list = $this->select();*/
		
	     return $list;
	     
	
	}
	
	
	//获取微访谈话题列表
	public function getInterviewList($status)
	{
		if($status==0){
		    $addstr = "";
		}else{
		    $addstr = " AND topics.status = '".($status-1)."'";
		}
		$list = $this->field('topic.name,topics.status,topics.content,topics.time_start,topics.time_end,topics.column,topics.pic')
		->table("{$this->tablePrefix}weibo_topics as topics
		LEFT JOIN {$this->tablePrefix}weibo_topic as topic
		ON topics.topic_id=topic.topic_id")
		->where('topics.isdel=0 AND topics.isvisit=1'.$addstr)	 
		->order('topics.time_start DESC')
		->findPage(8);
	 
	     return $list;
	
	
	 }
	 
	 //获取微访谈话题列表
	 public function getWonderfulList()
	 {
	 		
	 	$list = $this->field('topic.name,topics.status,topics.content,topics.time_start,topics.time_end,topics.column,topics.pic')
	 	->table("{$this->tablePrefix}weibo_topics as topics
	 	LEFT JOIN {$this->tablePrefix}weibo_topic as topic
	 	ON topics.topic_id=topic.topic_id
	 	LEFT JOIN {$this->tablePrefix}weibo_character as characters
	 	ON characters.topics_id = topics.topics_id")
	 	->where('topics.isdel=0 AND topics.isvisit=1  AND topics.isinterview=1')
	 	->order('topics.time_start DESC')
	 	->select();
	 
	 
	 	return $list;
	 
	 
	 }
	
	 //获取微访谈话题列表
	 public function getInterview()
	 {
	 		
	 	$list = $this->field('topic.topic_id,topic.name,topics.*')
	 	->table("{$this->tablePrefix}weibo_topics as topics
	 	LEFT JOIN {$this->tablePrefix}weibo_topic as topic
	 	ON topics.topic_id=topic.topic_id")
	 	->where('topics.isdel=0 AND topics.isvisit=1 ')
	 	->limit(2)
	 			->order('topics.time_start DESC')->select();
        foreach ($list as $key => $value) {
            $list[$key]['character'] = M("weibo_character")->where("topics_id = ".$list['topic_id'])->find();
        }
	    
	 
	 			return $list;
	 
	 
	 }
	 
	 //获取微访谈话题列表
	 public function getWonderful()
	 {
	 
	 $list = $this->field('topic.name,topics.topic_id,topics.status,topics.content,topics.time_start,topics.time_end,topics.column,topics.pic')
	 	->table("{$this->tablePrefix}weibo_topics as topics
	 	 	LEFT JOIN {$this->tablePrefix}weibo_topic as topic
	 	 	ON topics.topic_id=topic.topic_id
	 	 	LEFT JOIN {$this->tablePrefix}weibo_character as characters
	 	 	ON characters.topics_id = topics.topics_id")
	 	 	->where('topics.isdel=0 AND topics.isvisit=1  AND topics.isinterview=1')
	 	->order('topics.time_start DESC')
	 	->limit(1)
	 	 	->select();
	 
	 
	 	return $list;
	 
	 
	 }
	
	
	//通过ID获取微访谈内容
	
	public function getTopicContent($topics_ID){
		
		$list = $this->field('content')->where('topics_id');
		
		return $list;
	}
	

	public function getHotLimit($p=0,$nums=10){
		$limit = $p*$nums.','.$nums;
		$list = $this->field('topic.name,topic.count,topics.domain,topics.note')
					 ->table("{$this->tablePrefix}weibo_topics as topics
					 		  LEFT JOIN {$this->tablePrefix}weibo_topic as topic
					 		  ON topics.topic_id=topic.topic_id")
					 ->where('topics.isdel=0')
					 ->order('topic.count desc')
					 ->limit($limit)
					 ->findAll();
		return $list;
	}
	// 获取专题详细信息
	public function getTopics($name = null, $topics_id = null, $domain = null, $recommend = false)
	{
		if ($name) {
			$name = html_entity_decode(urldecode($name), ENT_QUOTES);
			$map['topic_id'] = D('Topic', 'weibo')->getTopicId($name);
		} else if($topics_id) {
			$map['topics_id'] = intval($topics_id);
		} else if ($domain) {
			$map['domain'] = h(t($domain));
		} else {
			return false;
		}
		//$recommend && $map['recommend'] = '1';
		$map['isdel'] = 0;
		$topics = D('Topics', 'weibo')->where($map)->find();
	    $res = M('weibo_character')->where("topics_id=".$topics_id)->select();
	    $topics['introduce'] = getSubByKey($res,'introduce');
        $topics['introduce'] = $topics['introduce'][0];
		if ($topics) {
			$topics['name'] = $name ? t($name) : D('Topic', 'weibo')->getField('name', "topic_id={$topics['topic_id']}");
		}
		return $topics;
	}

	// 删除专题
	public function deleteTopics($topics_id)
	{
		$topics_id = is_array($topics_id) ? $topics_id : explode(',', $topics_id);
		$map['topics_id'] = array('IN', $topics_id);
		$res = $this->setField('isdel', '1', $map);
		return $res;
	}
	// 删除微访谈
	public function deleteInterview($topics_id)
	{
		$topics_id = is_array($topics_id) ? $topics_id : explode(',', $topics_id);
		$map['topics_id'] = array('IN', $topics_id);
		
		$res = $this->setField('isdel', '1', $map);
		return $res;
	}

	// 推荐专题
	public function recommendTopics($topics_id, $recommend = true)
	{
		$topics_id = is_array($topics_id) ? $topics_id : explode(',', $topics_id);
		$map['topics_id'] = array('IN', $topics_id);
		$res = $this->setField('recommend', $recommend ? '1' : '0', $map);
		return $res;
	}
	
	// 推荐微访谈
	public function recommendInterview($topics_id, $recommend = true)
	{
		$topics_id = is_array($topics_id) ? $topics_id : explode(',', $topics_id);
		$map['topics_id'] = array('IN', $topics_id);
		$res = $this->setField('isvisit', $recommend ? '1' : '0', $map);
		return $res;
	}
	
	// 推荐精彩访谈
	public function recommendWonderful($topics_id, $recommend = true)
	{
		$topics_id = is_array($topics_id) ? $topics_id : explode(',', $topics_id);
		$map['topics_id'] = array('IN', $topics_id);
		$res = $this->setField('isinterview', $recommend ? '1' : '0', $map);
		return $res;
	}
    
	// 推荐精彩回顾
    public function isSetWonderful($topics_id, $recommend = true)
    {
        $map['topics_id'] = array('IN', $topics_id);
        $res = $this->setField('iswonderful', $recommend ? '1' : '0', $map);
        return $res;
    }
    
	
}