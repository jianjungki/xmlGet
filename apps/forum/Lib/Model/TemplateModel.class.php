<?php 
class TemplateModel extends Model {
	protected $tableName = "forum_template_type";
		
	public function getTemplateOnlyInfo($id){
		$map['id'] = array('in',$id);
		return $this->where($map)->findAll();
	}
}
