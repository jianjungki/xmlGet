<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// survey数据
	"DROP TABLE IF EXISTS `{$db_prefix}survey`;",
	"DROP TABLE IF EXISTS `{$db_prefix}survey_question_link`;",
	"DROP TABLE IF EXISTS `{$db_prefix}survey_question`;",
	"DROP TABLE IF EXISTS `{$db_prefix}survey_answer`;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'survey'",
	// 模板数据
	"DELETE FROM `{$db_prefix}template` WHERE `name` = 'survey_create_weibo' OR `name` = 'survey_share_weibo';",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'survey';",
);

foreach ($sql as $v) {
	$res = M('')->execute($v);
}