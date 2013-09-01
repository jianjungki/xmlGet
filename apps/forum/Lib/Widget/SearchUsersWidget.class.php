<?php
class SearchUsersWidget extends Widget{
	
	public function render($data){
		if(is_array($data['userId'])) {
			$data['userId'] = array_filter($data['userId']);
			$userCount = empty($data['userId']) ? 0 : count($data['userId']);
		} else {
			$userCount = empty($data['userId']) ? 0 : 1;
		}
		$data['userCount'] = $userCount;
		
		if(isset($data['userId'])) {
			if(is_array($data['userId'])) {
				if(!empty($data['userId'])) {
					$data['userId'] = implode(',', $data['userId']);
				}
			}
		}
		
		$content = $this->renderFile(dirname(__FILE__)."/SearchUsers.html",$data);
		return $content;
	}
}
?>