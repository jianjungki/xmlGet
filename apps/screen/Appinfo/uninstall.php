<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// group数据
	"DROP TABLE IF EXISTS `{$db_prefix}screen`;",
    "DROP TABLE IF EXISTS `{$db_prefix}screen_weibo;",
);

foreach ($sql as $v)
	M('')->execute($v);