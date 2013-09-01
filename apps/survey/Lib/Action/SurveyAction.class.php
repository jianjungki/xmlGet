<?php
class SurveyAction extends Action
{
	public function index()
	{
		$this->survey();
	}

	//渲染调查问卷
	public function survey()
	{
		$survey_id = $_GET['id'];

		$survey    = D('Survey', 'survey')->getSurvey($survey_id);
		$questions = D('Survey', 'survey')->getSurveyQuestions($survey_id);
		if ($answer = D('SurveyAnswer', 'survey')->getAnswer($survey_id, $this->mid)) {
			$this->assign('disabled', '1');
			$this->assign('answer', $answer[$this->mid]);
		}

		$this->assign($survey);
		$this->assign('list', $questions);
		$this->display('survey');
	}

	//回答调查问卷  前台表单提交处理
	public function submitSurvey()
	{
		if (D('SurveyAnswer', 'survey')->saveAnswer($this->mid, $_POST['survey_id'], $_POST['question'])) {
			$this->assign('jumpUrl', $_SERVER['HTTP_PREFERER']);
			$this->success(D('SurveyAnswer', 'survey')->getLastError());
		} else {
			$this->error(D('SurveyAnswer', 'survey')->getLastError());
		}
	}
}
