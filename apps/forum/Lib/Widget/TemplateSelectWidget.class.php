<?php
class TemplateSelectWidget extends Widget{
	public function render($data){
		if (isset ( $data ['widget'] ['data'] ) && ! empty ( $data ['widget'] ['data'] )) {
			$data ['widget'] ['data'] = getWidgetData ( $data ['widget'] ['data'] );
		}
		$result = $data['widget'];
		$result['hasData'] = $data['data'];
		$content = $this->renderFile(dirname(__FILE__)."/template/select.html",$result);
		return $content;		
    } 
	
    public function getData($data){    	
    	return explode('<br />',nl2br($data));;
    }
}
?>