<?php 
class CoreModel extends Model{
	
	//获取论坛分类列表
	function getCategory(){
		$this->cateService = service ( "Category" );
		$cate = $this->cateService->getCategoryList ();
		return $cate;
	}

	//【杨德升添加】获取板块下的分类. $board为空时返回所有的板块分类 
	function getCateInBoard($board = '', $order = 'tagid ASC, ordernum ASC') {
		$tag_model  = M('x_tag');
		$cate_model = M('ts_forum_tclass');
		$map = array();
		$res = array();
		if(!empty($board)) {
			$map['tagid'] = array('in', $board);
		}
		$r = $cate_model->where($map)->order($order)->findAll();
		foreach($r as $v1) {
			
			if(isset($res[$v1['tagid']])) continue;

			$tag_name = $tag_model->where('`tagid` = ' . intval($v1['tagid']))->field('tagname')->limit(1)->findAll();
			$tag_name = $tag_name[0]['tagname'];
			$res[$v1['tagid']]['name'] = $tag_name;
			unset($tag_name);
			
			$cate = array();
			foreach($r as $v2) {
				if($v1['tagid'] != $v2['tagid']) continue;
				$cate[] = array(
					'id'    => $v2['id'],
					'name'  => $v2['name'],
					'order' => intval($v2['ordernum'])
				);
			}
			$res[$v1['tagid']]['cate'] = $cate;
			unset($cate);

		}
		return $res;
	}
}
?>