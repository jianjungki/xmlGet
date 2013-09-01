<?php

class ForumApiModel extends Model {

	private $topic;
	private $post;
	private $credit;

	// 初始化
	public function _initialize() {
		$this->topic = D('topic', 'forum');
		$this->post = D('post', 'forum');
		$this->credit = service('Credit');
	}

	// 发帖
	public function postTopic($title, $content, $class, $category, $uid) {
		// 验证表单数据的正确性
		$title = htmlspecialchars($title, ENT_QUOTES);
		if(isset($title)) {
			$testtest = trim(t($title));
			if(empty($testtest) && $testtest != 0) {
				return null;
			}
			unset($testtest);
		}
		if(isset($content)) {
			$content = str_replace("<p>", "", $content);
			$content = str_replace("</p>", "<br>", $content);
			$testtest = trim($this->_deleteBr(t($content)));
			if(empty($testtest) && $testtest !== 0) {
				return null;
			}
			unset($testtest);
		}
		if(isset($class)) {
			if(empty($class)) {
				return null;
			}
		}

		$time = time();
		$topicAdd['fid'] = $class;
		$topicAdd['uid'] = $uid;		
		$topicAdd['cTime'] = $time;
		$topicAdd['title'] = $title;
		$maskName = M('user')->field('uid, uname')->where('uid='.$uid)->find();
		$topicAdd['maskId'] = $maskName['uid'];
		$topicAdd['maskName'] = $maskName['uname'];
		$topicAdd['rTime'] = $time;
		$topicAdd['tclass'] = intval($category);		
		$topicAdd['lastreuid'] = $uid;
		$topicAdd['lastMaskName'] = $maskName['uname'];

		$tid = $this->topic->add($topicAdd);
		if($tid) {
			$this->post->create();
			$postAdd['uid'] = $uid;
			$postAdd['tid'] = $tid;
			$postAdd['fid'] = $class;
			$postAdd['title'] = t($title);
			$postAdd['content'] = h($this->_deleteBr($content), 'base');
			$postAdd['istopic'] = 1;
			$postAdd['maskId'] = $maskName['uid'];
			$postAdd['maskName'] = $maskName['uname'];
			$postAdd['cTime'] = $time;
			$this->post->add($postAdd);

			$sql = "UPDATE `".C('DB_PREFIX')."forum` SET `lastpost_uid`={$uid},`topic_count`=topic_count+1,`lastpost_time`={$time},`lastpost_post_tid`={$tid} WHERE ( `fid` = '{$tclass}' )";
			M()->execute($sql);

			$this->credit->setUserCredit($uid, 'forum_post');
			return $tid;
		} else {
			return null;
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

	// 删帖
	public function delTopic($tid) {
		$map['tid'] = $tid;
		$save['isdel'] = 1;
		$result = $this->topic->where($map)->save($save);
		D('Commend', 'forum')->where($map)->delete();
		if($result) {
			$this->post->where($map)->save($save);
			$uid = $this->topic->where($map)->getField('uid');
			$this->credit->setUserCredit($uid, 'forum_delete_post');
			return $result;
		} else {
			return null;
		}
	}

	// 回复某个帖子
	public function replyTopic($tid, $uid, $content) {
		$tid = isset($tid) ? intval($tid) : 0;
		$topic = $this->topic->getTopic($tid);

		if(!$topic['tid']) {
			return null;
		} else if($topic['lock'] == 1) {
			return null;
		} else if($topic['isdel'] == 1) {
			return null;
		}

		if($quickreply == 'quickreply') {
			$content = trim($content['content']);
			$content = str_replace('\n', '<br>', $content);
		}
		$testtest = trim($this->_deleteBr($content));
		if($testtest !== 0 && empty($testtest)) {
			return null;
		}

		$postAdd['fid'] = $topic['fid'];
		$postAdd['tid'] = $topic['tid'];
		$postAdd['uid'] = $uid;
		$postAdd['content'] = $content;
		// $postAdd['title'] = t($title, ENT_QUOTES);
		$postAdd['title'] = '';
		$postAdd['ip'] = get_client_ip();
		$postAdd['cTime'] = time();
		$maskName = M('user')->field('uid, uname')->where('uid='.$uid)->find();
		$postAdd['maskId'] = $maskName['uid'];
		$postAdd['maskName'] = $maskName['uname'];
		$result = $this->post->add($postAdd);
		
		if($result) {
			$this->credit->setUserCredit($uid, 'forum_reply');
			$sql = "UPDATE `".C('DB_PREFIX')."forum_topic` SET `rTime`=".time().",`lastreuid`={$this->mid},`lastMaskName`='{$maskName}',replycount=replycount+1 WHERE tid={$topic['tid']}";
			$this->topic->execute($sql);
			return $result;
		} else {
			return null;
		}
	}

	// 对自己的帖子进行封贴
	public function notAllowReply($uid, $tid) {
		$map['uid'] = $uid;
		$map['tid'] = $tid;
		$isExist = $this->topic->where($map)->count();
		if($isExist) {
			$save['lock'] = 1;
			$result = $this->topic->where($map)->save($save);
			return $result;
		} else {
			return null;
		}
	}

	// 查看自己所有的帖子
	public function getAllMyTopics($uid) {
		$map['p.uid'] = $uid;
		$map['p.isdel'] = 0;
		$data = M()->Table('`'.C('DB_PREFIX').'forum_topic` AS t LEFT JOIN `'.C('DB_PREFIX').'forum_post` AS p ON t.tid = p.tid')
				   ->field('p.*')
				   ->where($map)
				   ->findAll();
		return $data;
	}

	// 查看自己所评论的所有帖子
	public function getAllMyCommentTopics($uid) {
		$map['uid'] = $uid;
		$map['isdel'] = 0;
		$data = $this->post->where($map)->limit($limit)->findAll();
		return $data;
	}

	// 查看帖子详细资料（浏览数、回复数、发布信息等）
	public function getForumDetail($tid) {
		$data = M()->Table('`'.C('DB_PREFIX').'forum_topic` AS topic LEFT JOIN `'.C('DB_PREFIX').'forum_post` AS post ON topic.tid = post.tid LEFT JOIN (`'.C('DB_PREFIX').'forum_special_topic` AS template, `'.C('DB_PREFIX').'forum_template_type` AS types) ON topic.tid = template.tid AND topic.special = types.id')
				   ->field('template.*,types.template,topic.tid,topic.fid,topic.tagid,topic.uid as topicUid,topic.maskFace,topic.maskId as topicMaskId,topic.maskName,topic.title,post.content,topic.tclass,topic.viewcount,topic.replycount,topic.dist,topic.lock,topic.top,topic.topX,topic.banzhu,topic.hide,topic.cTime,topic.rTime,topic.mTime,topic.lastMaskName,topic.lastreuid,topic.status,topic.isrecom,topic.isdel,topic.dig,topic.tags,topic.attach,topic.type,topic.displayorder,topic.highlight,topic.sign,topic.special,topic.affiche')
				   ->where('topic.tid='.$tid)
				   ->find();

		return $data;
	}
}