<?php
class SurveyAnswerModel extends Model
{
	protected $tableName = 'survey_answer';
	protected $_error    = null;

	public function getLastError()
	{
		return L($this->_error);
	}

	public function getMember($survey_id)
	{
		$map['survey_id'] = intval($survey_id);
		return $this->field('DISTINCT(`uid`),`mtime`')->where($map)->order('`mtime` DESC')->findAll();
	}

	public function getAnswer($survey_id, $uid = null, $order = null)
	{
		$uid && $map['uid']       = intval($uid);
		$map['survey_id'] = intval($survey_id);
		$answer = $this->where($map)->order($order)->findAll();
		$_answer = array();
		foreach ($answer as $a_k => $a_v) {
			$a_v['result'] = unserialize($a_v['result']);
			$_answer[$a_v['uid']][$a_v['question_id']] = $a_v;
			unset($answer[$a_k]);
		}
		return $_answer;
	}

	public function saveAnswer($uid, $survey_id, $answer)
	{
		$uid = intval($uid);
		$survey_id = intval($survey_id);
		$answer = $this->_escapeAnswer($answer);
		// 可否参加
		if (!$this->canJoin($uid, $survey_id)) {
			return false;
		} else if (!$this->isValidAnswer($survey_id, $answer)) {
			return false;
		}

		$mtime = time();
		foreach ($answer as $a_k => $a_v) {
			$question_id = intval($a_k);
			if ($question_id <= 0) {
				$this->_error = 'Invalid_Question';
				return false;
			}
			$result = $a_v;
			// 组装数据
			$answer[$a_k] = array(
			    'survey_id'   => $survey_id,
			    'question_id' => $question_id,
				'uid'         => $uid,
				'result'      => '\'' . serialize($result) . '\'',
				'mtime'       => $mtime
			);
			$answer[$a_k] = implode(',', $answer[$a_k]);
		}

		$answer = '(' . implode('),(', $answer) . ')';
		$sql = "INSERT INTO {$this->trueTableName} (`survey_id`,`question_id`,`uid`,`result`,`mtime`) VALUES" . $answer . ';';
		if (false !== $this->execute($sql)) {
			D('Survey', 'survey')->setInc('join_num', "`survey_id`={$survey_id}");
			$this->_error = 'Successfully_Submitted';			
			return true;
		} else {
			$this->_error = 'Failed_To_Submit';
			return false;
		}
	}

	// 用户可否参加
	public function canJoin($uid, $survey_id)
	{
		$uid = intval($uid);
		$survey_id = intval($survey_id);
		if ($this->getField('uid', "`survey_id`={$survey_id} AND `uid`={$uid}")) {
			$this->_error = 'Have_Joined';
			return false;
		} else if (!D('Survey', 'survey')->isValidSurvey($survey_id)) {
			$this->_error = D('Survey', 'survey')->getLastError();
			return false;
		} else {
			return true;
		}
	}

	// 答案是否有效
	public function isValidAnswer($survey_id, $answer)
	{
		$survey_id = intval($survey_id);
		$answer = $this->_escapeAnswer($answer);
		$map['survey_id'] = $survey_id;
		$num = M('survey_question_link')->where($map)->count();
		$map['question_id'] = array('IN', array_keys($answer));
		$submit_num = M('survey_question_link')->where($map)->count();
		if ($num != $submit_num) {
			$this->_error = 'Please_Fill_Out_The_Survey';
			return false;
		}
		return true;
	}

	private function _escapeAnswer($answer)
	{
		foreach ($answer as $k => $v) {
			$answer[$k] = is_array($v) ? $this->_escapeAnswer($v) : preg_replace('/^\s+|\s+$/', '', h($v));
			if (!$answer[$k]) {
				unset($answer[$k]);
			}
		}
		return $answer;
	}
}