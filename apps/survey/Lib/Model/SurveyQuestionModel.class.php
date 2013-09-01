<?php
class SurveyQuestionModel extends Model
{
	protected $tableName = 'survey_question';
	protected $_error    = null;
	protected $_widget   = array('CheckBox'=>'多选', 'Radio'=>'单选', 'TextArea'=>'简答');
	protected $_widget_default = 'TextArea';

	public function getLastError()
	{
		return L($this->_error);
	}

	public function getWidgetOptions()
	{
		return $this->_widget;
	}

	public function getQuestion($id = '')
	{
		if (intval($id) > 0) {
			$map['question_id'] = $id;
		} else {
			$this->_error = 'Invalid_ID';
			return false;
		}
		$result = $this->getQuestionList($map, null, '', 1, false);
		return $result[0];
	}

	public function getQuestionList($map = null, $field = null, $order = null, $limit = 20, $page = false)
	{
		$field = $field ? $field : '`question_id`,`title`,`description`,`widget`,`data`';
		$order = is_numeric($map['question_id']) ? '' : (isset($order) ? $order : '`question_id` DESC');
		$this->where($map)->field($field)->order($order);
		if ($page) {
			$question_list = $this->findPage($limit);
			$this->_parse($question_list['data']); // 引用
		} else {
			$question_list = $this->limit($limit)->findAll();
			$this->_parse($question_list); // 引用
		}
		return $question_list;
	}

	// 获取调查问卷的问题列表
	public function getQuestionListBySurveyId($survey_id)
	{
		$survey_id = intval($survey_id);
		$question_list = $this->table("`{$this->tablePrefix}survey_question_link` AS `l` LEFT JOIN `{$this->tablePrefix}survey_question` AS `q` ON `l`.`question_id`=`q`.`question_id`")
						 ->where("`l`.`survey_id`={$survey_id}")
						 ->order('`l`.`display_order` ASC')
						 ->findAll();
		$this->_parse($question_list); // 引用
		return $question_list;
	}

	private function _parse(& $list)
	{
		foreach ($list as &$v) {
			if (!$v['data']) {
				break;
			}
			$v['data'] = unserialize($v['data']);
		}
	}

	public function addQuestion($data)
	{
		$data = $this->_escapeData($data);
		if (false == $data) {
			return false;
		}

		$id = $this->add($data);
		if ($id) {
			return $id;
		} else {
			$this->_error = 'Failed_To_Add';
			return false;
		}
	}

	public function editQuestion($id, $data)
	{
		$data = $this->_escapeData($data);
		if (false == $data) {
			return false;
		}

		$id = intval($id);
		if ($id > 0) {
			$map['question_id'] = $id;
		} else {
			$this->_error = 'Invalid_ID';
			return false;
		}

		if (false !== $this->where($map)->save($data)) {
			return true;
		} else {
			$this->_error = 'Failed_To_Save';
			return false;
		}
	}

	private function _escapeData($data)
	{
		$data['title'] = h(t(preg_replace('/^\s+|\s+$/', '', $data['title'])));
		$data['description'] = h($data['description']);
		$data['data'] = serialize($this->_formatOptions($data['data']));
		$data['widget'] = (!in_array($data['widget'], $this->_widget) || !$data['data'])? $data['widget'] : $this->_widget_default;
		if (!$data['title']) {
			$this->_error = 'Title_Empty';
			return false;
		}
		return $data;
	}

	private function _formatOptions($data)
	{
		$_data = array();
		foreach ($data as $d_k => $d_v) {
			$d_v = h(t(preg_replace('/^\s+|\s+$/', '', $d_v)));
			if ($d_v) {
				$key = intval($d_k);
				$_data[$key] = $d_v;
			}
		}
		return $_data;
	}

	public function deleteQuestion($ids)
	{
		$ids = is_array($ids) ? $ids : explode(',', $ids);

		if (empty($ids)) {
			$this->_error = 'No_Selection';
			return false;
		} else {
			$map['question_id'] = array('IN', $ids);
		}

		if (false !== D('SurveyAnswer', 'survey')->where($map)->delete()
			&& false !== $this->where($map)->delete()) {
			return true;
		} else {
			$this->_error = 'Failed_To_Delete';
			return false;
		}
	}
}