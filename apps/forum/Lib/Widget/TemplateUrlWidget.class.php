<?php
class TemplateUrlWidget extends Widget{
	public function render($data){
		$result = $data['widget'];
		$result['hasData'] = $data['data'];
		$content = $this->renderFile(dirname(__FILE__)."/template/url.html",$result);
		return $content;		
    } 
	
    public function getData($data){    	
    	return explode('<br />',nl2br($data));;
    }
}
?>