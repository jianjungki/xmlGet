<?php 
class ClassIfyModel extends Model{
	var $tableName = 'bbs_classify';
	
	//添加主题
	function doadd($tid,$class){
		$class = explode( ',' , $class );
		$data['tagid'] = intval( end( $class ) );
		$data['tid']   = intval( $tid );
		foreach ( $class as $key=>$value ){
			$data['class'.($key+1)] = intval( $value );
		}
		$data['level'] = count( $class );
		$this->add($data);
	}
	
	//添加回复
	function doreply($tid , $class){
		/**
		$class = explode( ',' , $class );
		foreach ( $class as $key=>$value ){
			$map['class'.($key+1)] = $value;
		}
		$map['tid'] = $tid;
		$this->doAddCount( $tid, $map , 1 );  //更新今日回复
		**/
	}
	
	//更新分类主题统计表
	function doAddCount( $tid , $data ,$type ){
		/**
		$data['tid']   = $tid ;
		$data['type']  = $type ;
		$data['cTime'] = time() ;
		$this->add( $data );
		**/
	}
	
	function upBoard( $tid ,$board ){
		$arrBoard = explode( ',',$board );
		foreach($arrBoard as $key=>$value){
			$data['class'.($key+1)] = $value;
		}
		$data['tagid'] = end($arrBoard);
		$map["tid"]    = array( "IN",$tid );
		$this->where($map)->data($data)->save();
		//dump($this->getLastSql());
	}
	
	//获取版块下的总数信息
	function getTotalCount( $class ){
		$class = explode( ',' , $class );
		foreach ( $class as $key=>$value ){
			$map[] = 'class'.($key+1).'='.$value;
		}
		$map = implode(' AND ',$map);
		$result =  $this->query("SELECT count(1) as theme,sum(replycount) as reply FROM ts_bbs_topic WHERE id IN (SELECT tid FROM ts_bbs_classify WHERE $map)");
		$result[0]['reply'] = intval( $result[0]['reply'] ); 		
		return $result[0];
	}
	
	//获取版块下今日的信息
	function getTodayCount( $class ){
		$today = getdate(time());
		$today = mktime(0,0,0,$today['mon'],$today['mday'],$today['year']);

		$class = explode( ',' , $class );
		foreach ( $class as $key=>$value ){
			$map[] = 'class'.($key+1).'='.$value;
		}
		
		$map = implode(' AND ',$map);
		$themenum = $this->query("SELECT count(1) as theme FROM ts_bbs_topic WHERE id IN (SELECT tid FROM ts_bbs_classify WHERE $map) AND cTime>$today");;
		$reply = $this->query("SELECT count(1) as theme FROM ts_bbs_post WHERE tid IN (SELECT tid FROM ts_bbs_classify WHERE $map) AND cTime>$today AND istopic=0");;
		$result['theme'] = $themenum[0]['theme'];
		$result['reply'] = $reply[0]['theme'];
		return $result;	
	}
}
?>