<?php
class FilterWordModel extends Model {
	protected $tableName = 'forum_filter_word';
	protected static $filterWord = array ();
	public function addWords($tagname) {
		$tagname = str_replace ( array (' ', '，', ';', '；', '|' ), ',', $tagname );
		$tagname = str_replace ( '*', '', $tagname );
		$tagnames = explode ( ',', $tagname );
		foreach ( $tagnames as $k => $v ) {
			if ($v) {
				$map ['name'] = $v;
				$word = $this->where ( $map )->find ();
				if ($word) {
					return $word ['id'];
				} else {
					$map ['ordernum'] = strlen ( $v );
					$res = $this->add ( $map );
					$tags [$k] = $res;
				}
			}
		}
		return $tags;
	}
	
	public function filter($content) {
		if (empty ( self::$filterWord )) {
			if(C ( 'MEMCACHED_ON' )){
				$cache = service('Cache');
				$filterWord = $cache->getFilterWord();
				if(!$filterWord){
					$filterWord = $this->getFilterWordArray();
					$cache->setFilterWord($filterWord);
				}
				self::$filterWord = $filterWord;
			}else{
				self::$filterWord = $this->getFilterWordArray();
			}
		}
		
		$temp = str_ireplace ( self::$filterWord, "*", $content );
		return $temp;
	}
	
	private function getFilterWordArray() {
		$filter = $this->order ( 'ordernum DESC' )->findAll ();
		foreach ( $filter as &$value ) {
			$value ['name'] = $value ['name'];
		}
		return getSubByKey ( $filter, 'name' );
	}
	
	//获取标签ID
	public function getTagId($tagname) {
		//解析输入
		if (is_array ( $tagname )) {
			$tagnames = $tagname;
			$returnType = 'array';
		} else {
			$tagname = str_replace ( array (' ', '，', ';', '；' ), ',', $tagname );
			$tagnames = explode ( ',', $tagname );
			$returnType = 'string';
		}
		
		//获取TagId
		foreach ( $tagnames as $k => $v ) {
			$tags [$k] = $this->addTag ( $v );
		}
		if ($returnType == 'array') {
			return $tags;
		} else if ($returnType == 'string') {
			return implode ( ',', $tags );
		}
	}
}
?>