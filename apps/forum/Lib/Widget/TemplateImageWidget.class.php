<?php
class TemplateImageWidget extends Widget{
	public function render($data){
		$content = $this->renderFile(dirname(__FILE__)."/template/image.html",$data);
		return $content;		
    } 
	
    public function getData($data){    	
    	return explode('<br />',nl2br($data));;
    }
}
?>