
DROP TABLE IF EXISTS `ts_screen`;

CREATE TABLE `ts_screen` (
  `screen_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '大屏幕的主键',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '大屏幕的名字',
  `uid` int(11) NOT NULL COMMENT '大屏幕发起人的uid',
  `time_start` int(11) NOT NULL DEFAULT '0' COMMENT '大屏幕的开始时间',
  `time_end` int(11) NOT NULL DEFAULT '0' COMMENT '大屏幕的结束时间',
  `explains` text NOT NULL COMMENT '大屏幕的描述信息',
  `logo` int(255) NOT NULL COMMENT '大屏幕的logoId',
  `cTime` int(11) NOT NULL COMMENT '大屏幕的创建时间',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '大屏幕当前状态，暂无使用，预留',
  `keyword` varchar(255) NOT NULL DEFAULT '' COMMENT '大屏幕绑定的关键词',
  `topic_id` int(11) NOT NULL COMMENT '大屏幕绑定的话题',
  PRIMARY KEY (`screen_id`),
  KEY `time` (`status`,`time_start`,`time_end`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_screen_weibo`;

CREATE TABLE `ts_screen_weibo` (
  `weibo_id` int(11) NOT NULL COMMENT '上墙的weiboId,关联ts_weibo',
  `screen_id` int(11) NOT NULL COMMENT '大屏幕的Id,关联ts_screen',
  `cTime` int(11) NOT NULL COMMENT '上墙时间',
  `uid` int(11) NOT NULL COMMENT '设置这个操作的用户id',
  `status` enum('0','1') NOT NULL DEFAULT '0' COMMENT '上墙的状态。0为未上墙，1为已上墙',
  PRIMARY KEY (`weibo_id`),
  UNIQUE KEY `weibo_id` (`weibo_id`,`screen_id`),
  KEY `weibo` (`screen_id`,`status`,`weibo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

