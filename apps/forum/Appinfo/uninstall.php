<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// group数据
	"DROP TABLE IF EXISTS `{$db_prefix}forum`;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_admin_rule;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_affiche;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_attach;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_commend;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_log;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_login_log;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_post;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_setting;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_sign;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_sign_post;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_special_topic;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_tclass;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_template_type;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_template_widget;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_topic;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_user_group;",
    "DROP TABLE IF EXISTS `{$db_prefix}forum_user_rule;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'forum'",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'forum';",
);

foreach ($sql as $v)
	M('')->execute($v);