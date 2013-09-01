<?php
/*
 * @param $data['id']
 * @param $data['data']
 * @param $data['result']
 * @param $data['disabled']
 */
class RadioWidget extends Widget{
	public function render($data){
		$data['disabled'] = $data['disabled'] ? 'disabled' : null;
		return $this->renderFile('Radio',$data);
	}
}