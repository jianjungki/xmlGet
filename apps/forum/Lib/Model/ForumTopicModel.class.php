<?php 
class ForumTopicModel extends Model {
	protected $tableName = "forum_topic";
		
	public function changeForum($fid,$newFid){
		$map['fid'] = intval($fid);
		$save['fid'] = intval($newFid);
		//转移并删除原先的帖子
		$result = $this->where($map)->save($save);
		//TODO 需要把帖子分类也更改掉
		return $result;
	}
	
}
