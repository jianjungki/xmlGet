<?php 
class ForumModel extends Model {
	protected $tableName = "forum";
	public function getCount($key,$fid){
		$data = $this->getData($fid);
		return $data[$key];
	}
	
	private function getData($fid){
		$map['fid'] = intval($fid);
		$data = $this->where($map)->find();
		return $data;
	}

	


	public function deleteForum($fid,$newFid,$transfer,$service){
		$map['fid'] = $fid;
		if($transfer){
			//更改帖子的分类
			$result = D('ForumPost')->changeForum($fid,$newFid);
			$result = D('ForumTopic')->changeForum($fid,$newFid);
		}
		$result = $service->deleteCategory($fid);
		//$result = $this->where($map)->delete();
		return $result;
	}
	
	
	public function getBlockList($cate){
		foreach ($cate as $key=>$value){
			if(!empty($value['children'])){
				foreach($value['children'] as $k=>$val){
					$data[]['id'] = $val['id'];
				}
			}
		}

	}
	
}

