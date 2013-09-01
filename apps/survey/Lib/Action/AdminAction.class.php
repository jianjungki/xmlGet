<?php
import ( 'admin.Action.AdministratorAction' );
class AdminAction extends AdministratorAction
{
	public function index()
	{
		$this->surveyList();
	}

	/* 问卷管理 */
	public function surveyList()
	{
		$list = D('Survey', 'survey')->getSurveyList(null, null, '`survey_id` DESC', 20, true);

		$this->assign('list', $list);
		$this->display('surveyList');
	}

	// 编辑调查问卷
	public function editSurvey()
	{
		if ($_GET['id']) {
			$survey    = D('Survey', 'survey')->getSurvey($_GET['id']);
			$questions = D('Survey', 'survey')->getSurveyQuestions($_GET['id']);
			if (!$survey) {
				$this->error(D('Survey', 'survey')->getLastError());
			}
			$this->assign('id', intval($_GET['id']));
			$this->assign($survey);
			$this->assign('list', $questions);
		}
		$this->display();
	}

	// 创建/保存调查问卷
	public function saveSurvey()
	{
		if ($_POST['id']) {
			$survey_id = $_POST['id'];
			$result = D('Survey', 'survey')->editSurvey($survey_id, $_POST);
			$result && $result = D('Survey', 'survey')->saveSurveyQuestionsOrder($survey_id, $_POST['question_id']);
			$log = "编辑问卷(ID{$survey_id})成功";
		} else {
			$survey_id = $result = D('Survey', 'survey')->addSurvey($_POST);
			$log = "添加问卷(ID{$result})成功";
		}
		if ($result) {
			/*service('Log')->info("{$GLOBALS['ts']['user']['name']}(UID:{$GLOBALS['ts']['user']['uid']})-"
							 	. "{$model}管理-" . $log);*/			
			/*if ($_POST['id']) {
				$this->assign('jumpUrl', U('survey/Admin/editSurvey', array('id'=>$survey_id)));
				$this->success('操作成功');
			} else {*/
			/*创建Survey成功后跳转到编辑问题页
			编辑Survey成功后跳转后查看页
			*/
			if($_POST['id'])	redirect(U('survey/Admin/surveyResult', array('id'=>$survey_id, 'status'=>1)));
			else redirect(U('survey/Admin/editSurvey', array('id'=>$survey_id, 'status'=>1)));
			/*}*/
		} else {
			$this->assign('jumpUrl', U('survey/Admin/editSurvey', array('id'=>$survey_id)));
			$this->error(D('Survey', 'survey')->getLastError());
		}
	}

	//删除调查问卷
	public function deleteSurvey()
	{
		$result = D('Survey', 'survey')->deleteSurvey($_POST['id']);
		if ($result) {
			/*service('Log')->info("{$GLOBALS['ts']['user']['name']}(UID:{$GLOBALS['ts']['user']['uid']})-"
							 	 . "问卷管理-" . '删除ID' . $_POST['id'] . ' ' . $dao->getLastError());*/
			$status = 1;
		} else {
			/*service('Log')->warn("{$GLOBALS['ts']['user']['name']}(UID:{$GLOBALS['ts']['user']['uid']})-"
							 	 . "问卷管理-" . '删除ID' . $_POST['id'] . ' ' . $dao->getLastError());*/
			$status = 0;
		}
		$this->ajaxReturn('', D('Survey', 'survey')->getLastError(), $status);
	}

	/* 问题管理 */
	public function questionList()
	{
		$list = D('SurveyQuestion', 'survey')->getQuestionList(null, '`question_id`,`title`', '`question_id` DESC', 20, true);

		$this->assign('list', $list);
		$this->display();
	}

	// 创建/编辑问题
	public function editQuestion()
	{
		if ($_GET['id']) {
			$question = D('SurveyQuestion', 'survey')->getQuestion($_GET['id']);
			if (!$question) {
				$this->error(D('SurveyQuestion', 'survey')->getLastError());
			}

			$this->assign('id', intval($_GET['id']));
			$this->assign($question);
		}
		$this->assign('survey_id', intval($_GET['survey_id']));
		$this->assign('widget_options', D('SurveyQuestion', 'survey')->getWidgetOptions());
		$this->display();
	}

	// 保存问题
	public function saveQuestion()
	{
		if ($_POST['question_id']) {
			$question_id = $_POST['question_id'];
			$result = D('SurveyQuestion', 'survey')->editQuestion($question_id, $_POST);
			$log = "编辑问题(ID{$question_id})成功";
		} else {
			$question_id = $result = D('SurveyQuestion', 'survey')->addQuestion($_POST);
			if ($question_id && $_POST['survey_id']) {
				D('Survey', 'survey')->addSurveyQuestion($_POST['survey_id'], $question_id);
			}
			$log = "添加问题(ID{$result})成功";
		}
		if ($result) {
			/*service('Log')->info("{$GLOBALS['ts']['user']['name']}(UID:{$GLOBALS['ts']['user']['uid']})-"
							 	. "{$model}管理-" . $log);*/
			//$this->assign('jumpUrl', $_SERVER['HTTP_REFERER']);
			//$this->success('操作成功');
			redirect($_SERVER['HTTP_REFERER'] . '&status=1');
		} else {
			$this->assign('jumpUrl', U('survey/Admin/editSurvey', array('id'=>$_POST['survey_id'])));
			$this->error(D('SurveyQuestion', 'survey')->getLastError());
		}
	}

	// 删除问题
	public function deleteQuestion()
	{
		if (isset($_POST['survey_id'])) {
			$result = D('Survey', 'survey')->deleteSurveyQuestion($_POST['survey_id'], $_POST['id']);
		} else {
			$result = D('SurveyQuestion', 'survey')->deleteQuestion($_POST['id']);
		}
		if ($result) {
			/*service('Log')->info("{$GLOBALS['ts']['user']['name']}(UID:{$GLOBALS['ts']['user']['uid']})-"
							 	 . "问卷管理-" . '删除ID' . $_POST['id'] . ' ' . $dao->getLastError());*/
			$status = 1;
		} else {
			/*service('Log')->warn("{$GLOBALS['ts']['user']['name']}(UID:{$GLOBALS['ts']['user']['uid']})-"
							 	 . "问卷管理-" . '删除ID' . $_POST['id'] . ' ' . $dao->getLastError());*/
			$status = 0;
		}
		$this->ajaxReturn('', D('SurveyQuestion', 'survey')->getLastError(), $status);
	}

	/* 调查结果 */
	public function surveyResult()
	{
		$survey = D('Survey', 'survey')->getSurvey($_GET['id']);
		$member = D('SurveyAnswer', 'survey')->getMember($_GET['id']);
		// 预缓用户信息
		D('User', 'home')->setUserObjectCache(getSubByKey($member, 'uid'));

		$this->assign('url', U('survey/Survey/survey', array('id'=>intval($_GET['id']))));
		$this->assign($survey);
		$this->assign('list', $member);
		$this->display();
	}

	// 详情
	public function survey()
	{
		$uid = intval($_GET['uid']);
		$survey_id = intval($_GET['id']);

		$survey    = D('Survey', 'survey')->getSurvey($survey_id);
		$questions = D('Survey', 'survey')->getSurveyQuestions($survey_id);
		if ($uid) {
			$answer = D('SurveyAnswer', 'survey')->getAnswer($survey_id, $uid);
			$this->assign('disabled', 'disabled');
			$this->assign('answer', $answer[$uid]);
		}

		$this->assign($survey);
		$this->assign('list', $questions);
		$this->display('../Survey/survey');
	}

	// 导出问卷统计结果
	public function exportSurvey()
	{
        $file_path = D('Survey', 'survey')->export($_GET['id']);
    	require_cache(SITE_PATH . '/addons/libs/Http.class.php');
		if (file_exists($file_path)) {
			$filename = iconv('utf-8', 'gbk', 'Survey.xls');
			Http::download($file_path, $filename);
		}else{
			$this->error(D('Survey', 'survey')->getLastError());
		}        
	}
}