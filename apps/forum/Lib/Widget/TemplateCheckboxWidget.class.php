<?php
class TemplateCheckboxWidget extends Widget {
	public function render($data) {
		if (isset ( $data ['widget'] ['data'] ) && ! empty ( $data ['widget'] ['data'] )) {
			$data ['widget'] ['data'] = getWidgetData ( $data ['widget'] ['data'] );
		}
		$result = $data['widget'];
		$result['hasData'] = $data['data'];
		$content = $this->renderFile ( dirname ( __FILE__ ) . "/template/checkbox.html", $result );
		return $content;
	}

}
?>