<?php 
class BoardModel extends Model{
	var $tableName = 'forum';
	
	
	//添加和保存专题分类
	function savetClass( $board,$newadd,$data){
		$board = end(explode(',',$board));
		if($newadd){
			foreach ($newadd as $key => $value){
				$map['tagid']    = $board;
				$map['name']     = trim( $value );
				if(!empty( $map['name'] ) && $this->table(C('DB_PREFIX').'forum_tclass')->where($map)->count()==0){
					$map['ordernum'] = 1000+$key;
					M('forum_tclass')->add($map);
				}			
			}
		}
		
		if($data){
			$i=1;
			foreach ($data as $key=>$value){
				M('forum_tclass')->setField('name',$value,"tagid='{$board}' AND id=$key");
				M('forum_tclass')->setField('ordernum',$i,"tagid='{$board}' AND id=$key");
				$i++;
			}
		}
	}
	
	function gettClassById($id){
		$map['id'] = array('in',$id);
		$data = M('forum_tclass')->where($map)->field('id,name,ordernum')->findAll();
		return $data;
	}
	
	
	function gettClass($class){
		
		// $list = F('forum_tclass_list_data_'.$class);
		// if(!$list){
			$list = M('forum_tclass')->where("tagid='{$class}'")->field('id,name,template')->order('ordernum ASC')->findall();
		
			// F('forum_tclass_list_data_'.$class,$list);
		// }
		if($list){
				foreach ($list as $key=>$value){
					$reslut[$value['id']] = array("name"=>$value['name'],"template"=>$value['template']);
				}
		}
		return $reslut;
	}
	
	
	//保存版主
	function saveBanZhu( $board,$users ){
		$map['tagid'] = $board;
		$map['banzhu']      = $users;
		if( $this->where('tagid='.$board)->count()==0 ){
			$result = $this->add($map);
		}else{
			$result = $this->save($map);
		}
		return $result;
	}
	
	//获取版块下的总数信息
	function getTotalCount(){
		$result =  $this->query("SELECT tagid,count(id) as theme,sum(replycount) as reply FROM ts_forum_topic GROUP BY tagid");	
		foreach($result as $value){
			$data[$value['tagid']]['theme'] = intval( $value['theme'] );
			$data[$value['tagid']]['reply'] = intval( $value['reply'] );
		}
		return $data;
	}
	
	//获取版块下今日的信息
	function getTodayCount( $class ){
			$today = getdate(time());
			$today = mktime(0,0,0,$today['mon'],$today['mday'],$today['year']);
			$arrclass = explode( ',' , $class );
			foreach ( $arrclass as $key=>$value ){
				$map[] = 'class'.($key+1).'='.$value;
			}
			$map = implode(' AND ',$map);
			$themenum = $this->query("SELECT tagid,count(1) as theme FROM ts_forum_topic WHERE cTime>$today GROUP BY tagid");;
			$reply = $this->query("SELECT a.tagid,count(b.id) as reply FROM ts_forum_topic a LEFT JOIN ts_forum_post b ON a.id=b.tid WHERE b.istopic=0 AND b.cTime>$today GROUP BY a.tagid");
			foreach($themenum as $value ){
				$data[$value['tagid']]['theme'] = $value['theme'];
			}
			foreach($reply as $value ){
				$data[$value['tagid']]['reply'] = $value['reply'];
			}
		return $data;
	}	
}
?>