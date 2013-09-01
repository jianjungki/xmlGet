<?php
class SurveyModel extends Model
{
	protected $tableName = 'survey';
	protected $_error    = null;

	public function getLastError()
	{
		return L($this->_error);
	}

	public function getSurvey($id = '', $field = null)
	{
		if (intval($id) > 0) {
			$map['survey_id'] = $id;
		} else {
			$this->_error = 'Invalid_ID';
			return false;
		}
		$result = $this->getSurveyList($map, $field, '', 1, false);
		return $result[0];
	}

	public function getSurveyList($map = null, $field = null, $order = null, $limit = 20, $page = false)
	{
		$map = array_map('h', $map);
		$field = $field ? $field : '`survey_id`,`title`,`description`,`status`,`mtime`,`end_time`,`join_num`';
		$order = is_numeric($map['survey_id']) ? '' : (isset($order) ? $order : '`survey_id` DESC');
		$this->where($map)->field($field)->order($order);
		if ($page) {
			$survey_list = $this->findPage($limit);
		} else {
			$survey_list = $this->limit($limit)->findAll();
		}
		return $survey_list;
	}

	public function getSurveyQuestions($survey_id)
	{
		return D('SurveyQuestion', 'survey')->getQuestionListBySurveyId($survey_id);
	}

	public function addSurvey($data)
	{
		$data = $this->_escapeData($data);
		if (false == $data) {
			return false;
		}

		$data['status'] = 1;
		$data['mtime']  = time();
		$id = $this->add($data);
		if ($id) {
			return $id;
		} else {
			$this->_error = 'Failed_To_Add';
			return false;
		}
	}

	public function editSurvey($id, $data)
	{
		$data = $this->_escapeData($data);
		if (false == $data) {
			return false;
		}

		$id = intval($id);
		if ($id > 0) {
			$map['survey_id'] = $id;
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
		if (!$data['title']) {
			$this->_error = 'Title_Empty';
			return false;
		}
		return $data;
	}

	public function deleteSurvey($ids)
	{
		$ids = is_array($ids) ? $ids : explode(',', $ids);

		if (empty($ids)) {
			$this->_error = 'No_Selection';
			return false;
		} else {
			$map['survey_id'] = array('IN', $ids);
		}

		if (false !== D('SurveyAnswer', 'survey')->where($map)->delete()
			&& false !== M('survey_question_link')->where($map)->delete()
			&& false !== $this->where($map)->delete()) {
			return true;
		} else {
			$this->_error = 'Failed_To_Delete';
			return false;
		}
	}

	// 检测调查问卷有效性
	public function isValidSurvey($survey_id)
	{
		$survey = $this->getSurvey($survey_id, '`status`,`end_time`');
		if (!survey) {
			$this->_error = 'Invalid_ID';
			return false;
		} else if (1 != $survey['status']) {
			$this->_error = 'Invalid_Survey';
			return false;
		} else if ($survey['end_time'] > 0 && $survey['end_time'] < time()) {
			$this->_error = 'Survey_Has_Ended';
			return false;
		} else {
			return $survey_id;
		}
	}

	/* 调查问卷-问题 */
	public function addSurveyQuestion($survey_id, $question_id)
	{
		$survey_id   = intval($survey_id);
		$question_id = intval($question_id);
		if ($survey_id <= 0 || $question_id <= 0) {
			$this->_error = 'Invalid_ID';
			return false;
		}

		$data['survey_id']     = $survey_id;
		$data['question_id']   = $question_id;
		$data['display_order'] = time();
		if (M('survey_question_link')->add($data)) {
			return true;
		} else {
			$this->_error = 'Failed_To_Add';
			return false;
		}
	}

	public function deleteSurveyQuestion($survey_id, $question_ids)
	{
		$survey_id   = intval($survey_id);
		$question_ids = is_array($question_ids) ? $question_ids : explode(',', $question_ids);
		if ($survey_id <= 0 || empty($question_ids)) {
			$this->_error = 'Invalid_ID';
			return false;
		}

		$map['survey_id']   = $survey_id;
		$map['question_id'] = array('IN', $question_ids);
		if (false !== D('SurveyAnswer', 'survey')->where($map)->delete()
			&& false !== M('survey_question_link')->where($map)->delete()) {
			return true;
		} else {
			$this->_error = 'Failed_To_Delete';
			return false;
		}
	}

	public function saveSurveyQuestionsOrder($survey_id, $question_ids)
	{
		$survey_id = intval($survey_id);
		$question_ids = is_array($question_ids) ? $question_ids : explode(',', $question_ids);
		if ($survey_id <= 0 || empty($question_ids)) {
			$this->_error = 'Invalid_ID';
			return false;
		}

		$now_order = M('survey_question_link')->field('`survey_id`,`question_id`,`display_order`')->where("`survey_id`={$survey_id}")->findAll();
		$new_order = @array_flip($question_ids);

		$res = true;
		$map['survey_id'] = $survey_id;
		foreach($now_order as $v){
			if($new_order[$v['question_id']] == $v['display_order'])continue;			
			$map['question_id'] = $v['question_id'];
			$_res = M('survey_question_link')->where($map)->save(array('display_order'=>intval($new_order[$v['question_id']])));
			$res = ($res && false !== $_res)? $res : false;
		}

		if (false === $res) {
			$this->_error = 'Failed_To_Sort';
		}		
		return $res;
	}

	/* 统计 */
	// 导出
	public function export($survey_id)
	{
		$survey_id = intval($survey_id);
        $xls_data   = array();

		$survey    = $this->getSurvey($survey_id);
		$questions = $this->getSurveyQuestions($survey_id);
		$answer = D('SurveyAnswer', 'survey')->getAnswer($survey_id, null, '`mtime` DESC');
		if (!$survey) {
			$this->_error = 'Failed_To_Export';
			return false;
		} else if (!$questions) {
			$this->_error = '该问卷没有题目';
			return false;
		}

		// 组装问题行
		$_questions = array('', 'email');
		$_q_ID      = 1;
		foreach ($questions as $k_v => $_q_v) {
			$_questions[] = $_q_ID++ . '. ' . html_entity_decode($_q_v['title'], ENT_QUOTES);
		}
		$_questions[] = '时间';
		$xls_data[0] = $_questions;

		// 预缓用户信息
		D('User', 'home')->setUserObjectCache(array_keys($answer));

		// 组装调查结果
		foreach ($answer as $a_k => $a_v) {
			$user_info = D('User', 'home')->getUserByIdentifier($a_k, 'uid');
			$_answer = array($user_info['uname'], $user_info['email']);
			foreach ($questions as $_q_v) {
				$result = array();
				if (is_array($a_v[$_q_v['question_id']]['result'])) {
					foreach ($a_v[$_q_v['question_id']]['result'] as $r_v) {
						$result[] = '(' . $r_v . ') ' . html_entity_decode($_q_v['data'][$r_v], ENT_QUOTES);
					}
					$_answer[] = implode("\n", $result);
				} else {
					$_answer[] = html_entity_decode($a_v[$_q_v['question_id']]['result'], ENT_QUOTES);
				}
				$mtime = $mtime ? $mtime : $a_v[$_q_v['question_id']]['mtime'];
			}
			$_answer[] = friendlyDate($mtime, 'ymd');
			$xls_data[] = $_answer;
		}

		// 导出Excel
        $file_name = md5($_SERVER['REQUEST_TIME'] . rand_string()) . '.xls';
        $file_path = UPLOAD_PATH . "/survey/{$file_name}";
		if (!file_exists(dirname($file_path))) {
			mkdir(dirname($file_path), 0777, true);
		}
        $file_path = service('Excel')->export($xls_data, $survey['title'], $file_path);
        return $file_path;
	}
}