<?php
class IndexAction extends Action
{
	public function index()
	{
		$this->surveyList();
	}

	public function surveyList()
	{
		$list = D('Survey', 'survey')->getSurveyList(null, null, '`survey_id` DESC', 20, true);

		$this->assign('list', $list);
		$this->display('list');
	}
}