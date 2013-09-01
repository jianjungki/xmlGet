<?php
/*
 * @param $data['id']
 * @param $data['data']
 * @param $data['result']
 * @param $data['disabled']
 */
class TextAreaWidget extends Widget{
	public function render($data){
		$data['disabled'] = $data['disabled'] ? 'readonly' : null;
		return $this->renderFile('TextArea',$data);
	}
}