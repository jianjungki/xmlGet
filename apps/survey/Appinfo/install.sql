
--
-- 表的结构 `ts_survey`
--

DROP TABLE IF EXISTS `ts_survey`;
CREATE TABLE IF NOT EXISTS `ts_survey` (
  `survey_id` smallint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL,
  `end_time` int(11) NOT NULL DEFAULT '0',
  `join_num` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`survey_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_survey_answer`
--

DROP TABLE IF EXISTS `ts_survey_answer`;
CREATE TABLE IF NOT EXISTS `ts_survey_answer` (
  `survey_id` smallint(8) unsigned NOT NULL,
  `question_id` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `result` text COLLATE utf8_unicode_ci NOT NULL,
  `mtime` int(11) NOT NULL,
  KEY `survey_id` (`survey_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `ts_survey_question`
--

DROP TABLE IF EXISTS `ts_survey_question`;
CREATE TABLE IF NOT EXISTS `ts_survey_question` (
  `question_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
  `description` text CHARACTER SET utf8,
  `widget` varchar(50) CHARACTER SET utf8 NOT NULL,
  `data` text CHARACTER SET utf8,
  PRIMARY KEY (`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_survey_question_link`
--

DROP TABLE IF EXISTS `ts_survey_question_link`;
CREATE TABLE IF NOT EXISTS `ts_survey_question_link` (
  `survey_id` smallint(8) unsigned NOT NULL,
  `question_id` int(11) unsigned NOT NULL,
  `display_order` int(11) DEFAULT NULL,
  KEY `survey_id` (`survey_id`),
  KEY `question_id` (`question_id`),
  KEY `display_order` (`display_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
	(0, 'survey', 'limitpage', 's:2:\"20\";', '2010-12-03 13:11:32'),
	(0, 'survey', 'defaultTime', 's:7:\"7776000\";', '2010-12-02 18:18:16'),
	(0, 'survey', 'join', 's:3:\"all\";', '2010-12-02 18:18:16');

#模板数据
DELETE FROM `ts_template` WHERE `name` = 'survey_create_weibo' OR `name` = 'survey_share_weibo';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
VALUES
	('survey_create_weibo', '发起投票', '', '我发起了一个投票:【{title}】 {url}', 'zh', 'survey', 'weibo', 0, 1290417734),
	('survey_share_weibo', '分享投票', '', '分享@{author} 的投票:【{title}】{url}', 'zh', 'survey', 'weibo', 0, 1290595552);

#积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'survey';
INSERT INTO `ts_credit_setting`  (`id`, `name`, `alias`, `type`, `info`, `score`, `experience`) 
VALUES
	('', 'add_survey', '发起投票', 'survey', '{action}{sign}了{score}{typecn}', '20', '20'),
	('', 'join_survey', '参与投票', 'survey', '{action}{sign}了{score}{typecn}', '1', '5'),
	('', 'joined_survey', '投票被参与', 'survey', '{action}{sign}了{score}{typecn}', '1', '1'),
	('', 'delete_survey', '删除投票', 'survey', '{action}{sign}了{score}{typecn}', '-20', '-20');