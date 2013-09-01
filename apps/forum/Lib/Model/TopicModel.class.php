<?php 
class TopicModel extends Model{
	var $tableName = 'forum_topic';
	
	protected $_validate = array(
		array('title','require','帖子标题不能为空'),
		array('category','require','请选择主题版块'),
		array('content','require','文章内容不能为空'),
	);


	//获取主题信息
	function getTopic($id){
		$info = $this->table("ts_forum_topic as topic
left join  (ts_forum_special_topic as template,ts_forum_template_type as types)
on topic.tid = template.tid  and topic.special = types.id ")->field("template.*,types.template,topic.tid,topic.fid,topic.tagid,topic.uid as topicUid,topic.maskFace,topic.maskId as topicMaskId,topic.maskName,topic.title,topic.tclass,topic.viewcount,topic.replycount,topic.dist,topic.lock,topic.top,topic.topX,topic.banzhu,topic.hide,topic.cTime,topic.rTime,topic.mTime,topic.lastMaskName,topic.lastreuid,topic.status,topic.isrecom,topic.isdel,topic.dig,topic.tags,topic.attach,topic.type,topic.displayorder,topic.highlight,topic.sign,topic.special,topic.affiche")->where('topic.tid='.$id)->find();
		return $info;
	}
	
	//获取TagId
	function getTags($tags){
		return model('Xtag')->formatTags($tags);
	}
	
	//设置精华 置顶  锁定等功能
	function setAdminStatus($class,$tid,$type,$status){
		$class = explode( ',' , $class );
		foreach ( $class as $key=>$value ){
			$map['class'.($key+1)] = $value;
		}
		$map['tid'] = $tid;
		 return $this->setField($type,$status,$map);
	}
	
	//彻底移除主题
	function dodelete($ids){
		$mapPost['tid'] = array('IN',$ids);
		$mapTopic['tid'] = array('IN',$ids);
		$this->where($mapTopic)->delete();
		M('forum_post')->where($mapPost)->delete();
	}
	

	function upBoard( $tid,$board ){
		$arrBoard = explode( ',',$board );
		foreach($arrBoard as $key=>$value){
			$data['class'.($key+1)] = $value;
		}
		$data['tagid'] = end( $arrBoard );
		$data['board'] = $board;
		$map['id'] = array("IN",$tid );
		$this->where($map)->data($data)->save();
	}
	
}
?>