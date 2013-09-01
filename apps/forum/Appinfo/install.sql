/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50516
Source Host           : 127.0.0.1:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50516
File Encoding         : 65001

Date: 2012-03-28 14:49:57
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `ts_forum`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum`;
CREATE TABLE `ts_forum` (
  `fid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL,
  `left_value` int(11) unsigned NOT NULL DEFAULT '0',
  `right_value` int(11) unsigned NOT NULL DEFAULT '0',
  `forum_logo` int(11) unsigned NOT NULL DEFAULT '0',
  `forum_icon` int(11) unsigned NOT NULL DEFAULT '0',
  `forum_intro` text,
  `forum_manager` tinytext,
  `view_count` int(11) NOT NULL DEFAULT '0',
  `topic_count` int(11) NOT NULL DEFAULT '0',
  `post_count` int(11) NOT NULL DEFAULT '0',
  `most_online_user` int(11) NOT NULL DEFAULT '0',
  `lastpost_uid` int(11) NOT NULL DEFAULT '0',
  `lastpost_time` int(11) NOT NULL DEFAULT '0',
  `lastpost_post_tid` int(11) NOT NULL DEFAULT '0',
  `lastpost_post_pid` int(11) NOT NULL DEFAULT '0',
  `today_thread_count` int(11) NOT NULL DEFAULT '0',
  `today_post_count` int(11) NOT NULL DEFAULT '0',
  `today_view_count` int(11) NOT NULL DEFAULT '0',
  `timeSetting` text,
  PRIMARY KEY (`fid`),
  KEY `left_value` (`left_value`),
  KEY `right_value` (`right_value`),
  KEY `left_value_2` (`left_value`,`right_value`)
) ENGINE=MyISAM AUTO_INCREMENT=906 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum
-- ----------------------------
INSERT INTO ts_forum VALUES ('1', 'root', '1', '24', '0', '0', null, null, '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null);

-- ----------------------------
-- Table structure for `ts_forum_admin_rule`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_admin_rule`;
CREATE TABLE `ts_forum_admin_rule` (
  `admingid` smallint(6) NOT NULL,
  `fid` int(11) NOT NULL,
  `allow_exalt` tinyint(1) NOT NULL DEFAULT '0',
  `allow_highlight` tinyint(1) NOT NULL DEFAULT '0',
  `allow_close` tinyint(1) NOT NULL DEFAULT '0',
  `allow_hide` tinyint(1) NOT NULL DEFAULT '0',
  `allow_commend` tinyint(1) NOT NULL,
  `allow_elite` tinyint(1) NOT NULL DEFAULT '0',
  `allow_top5` tinyint(1) NOT NULL DEFAULT '0',
  `allow_edit_special_thread` tinytext NOT NULL,
  `allow_edit_special_sign` tinytext NOT NULL,
  `allow_edit_thread` tinyint(1) NOT NULL DEFAULT '0',
  `allow_delete_thread` tinyint(1) NOT NULL DEFAULT '0',
  `allow_screen_thread` tinyint(1) NOT NULL DEFAULT '0',
  `allow_alarm_thread` tinyint(1) NOT NULL DEFAULT '0',
  `allow_grade_thread` tinyint(1) NOT NULL DEFAULT '0',
  `allow_read_report` tinyint(1) NOT NULL DEFAULT '0',
  `allow_check_attach` tinyint(1) NOT NULL DEFAULT '0',
  `allow_adjust_popedom` tinyint(1) NOT NULL DEFAULT '0',
  `allow_edit_report` tinyint(1) NOT NULL DEFAULT '0',
  `allow_tclass_manage` tinyint(1) NOT NULL DEFAULT '0',
  `allow_tip` tinyint(1) NOT NULL DEFAULT '0',
  `allow_changeCate` tinyint(1) NOT NULL DEFAULT '0',
  `allow_check_face` tinyint(1) NOT NULL DEFAULT '0',
  `allow_commend_popedom` tinyint(1) NOT NULL DEFAULT '0',
  `allow_find_trueuser` tinyint(1) NOT NULL DEFAULT '0',
  `allow_log_manage` tinyint(1) NOT NULL DEFAULT '0',
  `allow_all_tip` tinyint(1) NOT NULL DEFAULT '0',
  `allow_filter_word` tinyint(1) NOT NULL DEFAULT '0',
  `allow_online_count` int(1) NOT NULL,
  `allow_add_score` int(1) NOT NULL,
  `allow_banzhu` tinyint(1) NOT NULL DEFAULT '0',
  `allow_banzhu_biaoshi` tinyint(1) NOT NULL DEFAULT '0',
  KEY `tid_gid` (`admingid`,`fid`),
  KEY `gid` (`admingid`),
  KEY `fid` (`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_admin_rule
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_affiche`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_affiche`;
CREATE TABLE `ts_forum_affiche` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL DEFAULT '0',
  `ordernum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`,`ordernum`),
  KEY `ordernum` (`ordernum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_affiche
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_attach`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_attach`;
CREATE TABLE `ts_forum_attach` (
  `tid` int(11) NOT NULL,
  `attach_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_attach
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_commend`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_commend`;
CREATE TABLE `ts_forum_commend` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL DEFAULT '0',
  `ordernum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_commend
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_log`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_log`;
CREATE TABLE `ts_forum_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `content` varchar(250) NOT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `param` tinytext NOT NULL,
  `cTime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `uid` (`uid`),
  KEY `cTime` (`cTime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_log
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_login_log`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_login_log`;
CREATE TABLE `ts_forum_login_log` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `login_time` int(11) NOT NULL,
  `agent` char(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_login_log
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_post`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_post`;
CREATE TABLE `ts_forum_post` (
  `pid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(11) unsigned NOT NULL,
  `fid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) unsigned NOT NULL,
  `maskId` int(11) NOT NULL DEFAULT '0',
  `maskName` varchar(255) NOT NULL DEFAULT '0',
  `title` varchar(150) DEFAULT NULL,
  `content` longtext,
  `ip` char(16) DEFAULT NULL,
  `istopic` tinyint(1) NOT NULL DEFAULT '0',
  `cTime` int(11) NOT NULL DEFAULT '0',
  `muid` int(11) NOT NULL DEFAULT '0',
  `mTime` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `quote` int(11) unsigned NOT NULL DEFAULT '0',
  `isdel` tinyint(1) NOT NULL DEFAULT '0',
  `attach` text,
  `lastOptUid` int(11) NOT NULL DEFAULT '0',
  `banzhu` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`),
  KEY `cTime` (`cTime`),
  KEY `tid` (`tid`),
  KEY `uid` (`uid`,`istopic`),
  KEY `istopic` (`istopic`),
  KEY `fcount` (`fid`,`istopic`,`isdel`),
  KEY `fid` (`fid`,`uid`),
  KEY `maskName` (`maskName`),
  KEY `pid` (`pid`,`tid`),
  KEY `tid_2` (`tid`,`isdel`),
  KEY `fid_2` (`fid`,`istopic`,`isdel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_post
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_setting`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_setting`;
CREATE TABLE `ts_forum_setting` (
  `fid` int(11) NOT NULL,
  `image_state` tinyint(1) NOT NULL DEFAULT '0',
  `truename_state` tinyint(1) NOT NULL DEFAULT '0',
  `header_state` tinyint(1) NOT NULL DEFAULT '1',
  `dummy_state` tinyint(1) NOT NULL DEFAULT '0',
  `special_thread_type` tinytext NOT NULL,
  `special_thread_sign` tinytext NOT NULL,
  `score` tinyint(4) NOT NULL,
  `position` tinyint(1) NOT NULL,
  `affiche` text NOT NULL,
  `rule` text NOT NULL,
  `attach_size` int(11) DEFAULT NULL,
  `attach_type` char(200) DEFAULT NULL,
  `del_by_time` int(11) NOT NULL,
  KEY `tagid` (`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_setting
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_sign`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_sign`;
CREATE TABLE `ts_forum_sign` (
  `signid` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL,
  `icon` int(11) NOT NULL,
  `rule` tinytext NOT NULL,
  `dateline` int(11) NOT NULL,
  `position` int(1) NOT NULL DEFAULT '0',
  `fold` tinytext NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`signid`)
) ENGINE=MyISAM AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_sign
-- ----------------------------
INSERT INTO ts_forum_sign VALUES ('1', '全局置顶', '496515', '', '0', '5', '', '0');
INSERT INTO ts_forum_sign VALUES ('2', '版块置顶', '496516', '', '0', '6', '', '0');
INSERT INTO ts_forum_sign VALUES ('3', '锁帖', '496512', '', '0', '8', '', '0');
INSERT INTO ts_forum_sign VALUES ('5', '精华', '496513', '', '0', '1', '', '0');
INSERT INTO ts_forum_sign VALUES ('25', '图片', '496517', '', '0', '4', '', '0');
INSERT INTO ts_forum_sign VALUES ('27', '附件', '496510', '', '0', '12', '0', '0');
INSERT INTO ts_forum_sign VALUES ('28', '公告', '5371', '', '0', '10', '', '0');
INSERT INTO ts_forum_sign VALUES ('24', '火帖', '5373', '', '0', '3', '0', '0');
INSERT INTO ts_forum_sign VALUES ('23', '热门推荐', '496520', '', '0', '7', '', '0');
INSERT INTO ts_forum_sign VALUES ('77', '版主已有正式回复', '496519', '', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `ts_forum_sign_post`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_sign_post`;
CREATE TABLE `ts_forum_sign_post` (
  `signId` smallint(4) NOT NULL,
  `tid` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `uid` int(11) NOT NULL DEFAULT '0',
  `cTime` int(11) NOT NULL DEFAULT '0',
  KEY `signId` (`signId`),
  KEY `pid` (`tid`),
  KEY `relation` (`signId`,`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_sign_post
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_special_topic`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_special_topic`;
CREATE TABLE `ts_forum_special_topic` (
  `tid` int(11) NOT NULL,
  `special` int(11) NOT NULL DEFAULT '0',
  `specialData` text NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_special_topic
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_tclass`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_tclass`;
CREATE TABLE `ts_forum_tclass` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tagid` char(80) NOT NULL,
  `popedom` text,
  `name` char(80) NOT NULL,
  `ordernum` mediumint(6) NOT NULL,
  `template` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tagid` (`tagid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_tclass
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_template_type`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_template_type`;
CREATE TABLE `ts_forum_template_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `template` tinytext,
  `explain` varchar(255) NOT NULL DEFAULT '0',
  `sign` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(1) DEFAULT '0',
  `parse` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_template_type
-- ----------------------------
INSERT INTO ts_forum_template_type VALUES ('1', '公告', '1', null, '', '0', '1', '0');
INSERT INTO ts_forum_template_type VALUES ('12', '协会活动', '1', null, '0', '0', '0', '1');
INSERT INTO ts_forum_template_type VALUES ('6', '物品转让', '1', '8,10,9,11,15,12,13,3', '0', '0', '0', '1');
INSERT INTO ts_forum_template_type VALUES ('5', '二手交易', '1', null, '0', '0', '0', '1');
INSERT INTO ts_forum_template_type VALUES ('7', '房屋招租', '1', '16,17,18,19,20,21,22,23,28,13,3', '0', '0', '0', '1');
INSERT INTO ts_forum_template_type VALUES ('8', '征婚信息', '1', '29,7', '0', '0', '0', '1');
INSERT INTO ts_forum_template_type VALUES ('9', '拼的', '1', '7,1,2,3,4,5,6,8,9,10,11,12,13,15,16,17,18,19,20,21,22,23,24,25,26,27,28', '0', '0', '0', '1');
INSERT INTO ts_forum_template_type VALUES ('10', '失物招领', '1', '24,25,26,27,3', '0', '0', '0', '1');

-- ----------------------------
-- Table structure for `ts_forum_template_widget`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_template_widget`;
CREATE TABLE `ts_forum_template_widget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `widget` varchar(255) NOT NULL,
  `info` text,
  `data` text NOT NULL,
  `field` varchar(255) NOT NULL,
  `filed_rule` tinytext,
  `must` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_template_widget
-- ----------------------------
INSERT INTO ts_forum_template_widget VALUES ('1', '性别', 'radio', 'sdfasdf', '1  =   男\r\n2    =    女', 'sex', '', '1');
INSERT INTO ts_forum_template_widget VALUES ('2', '爱好', 'checkbox', '', '电影\r\n音乐\r\n游戏', 'love', '', '1');
INSERT INTO ts_forum_template_widget VALUES ('3', '联系方式', 'text', '', '', 'phone', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('4', '办公区域', 'select', '', 'A区\r\nB区\r\nC区\r\nD区\r\nE区\r\nF区', 'work', null, null);
INSERT INTO ts_forum_template_widget VALUES ('5', '个人空间', 'url', 'sdafasdf', '', 'space', null, null);
INSERT INTO ts_forum_template_widget VALUES ('6', '邮箱', 'email', '', '', 'email', null, null);
INSERT INTO ts_forum_template_widget VALUES ('7', '详细说明', 'textarea', '', '', 'infoData', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('8', '名称', 'text', '', '', 'obj_name', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('9', '规格型号', 'textarea', '', '', '48', null, null);
INSERT INTO ts_forum_template_widget VALUES ('10', '物品所在地', 'textarea', '', '', '45', null, null);
INSERT INTO ts_forum_template_widget VALUES ('11', '原    价', 'text', '', '', '49', null, null);
INSERT INTO ts_forum_template_widget VALUES ('12', '转让价格', 'text', '', '', '30', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('13', '联 系 人', 'text', '', '', '51', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('15', '购买时间', 'text', '', '', 'buydate', null, null);
INSERT INTO ts_forum_template_widget VALUES ('16', '招租类型', 'radio', '', '整房招租\r\n招合租', 'C1', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('17', '户型', 'select', '', '一房一厅\r\n二房一厅\r\n二房二厅\r\n三房一厅\r\n三房二厅\r\n四房一厅\r\n四房二厅\r\n五房一厅\r\n五房二厅\r\n其它', 'house_type', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('18', '出租面积', 'text', '单位平方', '', 'house_area', null, null);
INSERT INTO ts_forum_template_widget VALUES ('19', '位置', 'textarea', '', '', 'house_where', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('20', '附属家具、电器', 'textarea', '', '', 'house_what', null, null);
INSERT INTO ts_forum_template_widget VALUES ('21', '租金', 'text', '元/月', '', 'house_money', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('22', '管理费', 'text', '元/月', '', 'house_admin_money', null, null);
INSERT INTO ts_forum_template_widget VALUES ('23', '可入住时间', 'text', '', '', 'house_cTime', null, null);
INSERT INTO ts_forum_template_widget VALUES ('24', '拾到(或丢失)物品时间', 'text', '', '', 'drop_time', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('25', '拾到(或丢失)物品地点', 'textarea', '', '', 'drop_where', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('26', '拾到(或丢失)物品描述', 'textarea', '', '', 'drop_detail', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('27', '拾到(或丢失)联系人', 'text', '', '', 'drop_who', null, '1');
INSERT INTO ts_forum_template_widget VALUES ('28', '招租要求', 'textarea', '', '', 'C2', null, null);
INSERT INTO ts_forum_template_widget VALUES ('29', '我承诺', 'radio', '', '我所发布的征婚人信息均属实，我可为其提供诚信担保。', 'promise', null, '1');

-- ----------------------------
-- Table structure for `ts_forum_topic`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_topic`;
CREATE TABLE `ts_forum_topic` (
  `tid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL,
  `tagid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) unsigned NOT NULL,
  `maskFace` tinytext NOT NULL,
  `maskId` int(11) NOT NULL DEFAULT '0',
  `maskName` varchar(255) NOT NULL DEFAULT '0',
  `title` char(80) NOT NULL,
  `tclass` mediumint(8) NOT NULL,
  `viewcount` int(11) unsigned NOT NULL DEFAULT '0',
  `replycount` int(11) unsigned NOT NULL DEFAULT '0',
  `dist` tinyint(1) NOT NULL DEFAULT '0',
  `top` tinyint(1) NOT NULL DEFAULT '0',
  `banzhu` tinyint(1) NOT NULL DEFAULT '0',
  `topX` tinyint(4) NOT NULL,
  `lock` tinyint(1) NOT NULL DEFAULT '0',
  `hide` tinyint(1) NOT NULL DEFAULT '0',
  `cTime` int(11) NOT NULL DEFAULT '0',
  `rTime` int(11) NOT NULL DEFAULT '0',
  `mTime` int(11) unsigned NOT NULL DEFAULT '0',
  `lastreuid` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `isrecom` tinyint(1) NOT NULL DEFAULT '0',
  `isdel` tinyint(1) NOT NULL DEFAULT '0',
  `dig` int(8) NOT NULL,
  `tags` varchar(200) NOT NULL,
  `attach` tinyint(1) NOT NULL DEFAULT '0',
  `type` int(6) unsigned NOT NULL DEFAULT '0',
  `displayorder` tinyint(1) NOT NULL DEFAULT '0',
  `highlight` char(10) NOT NULL DEFAULT '0',
  `sign` tinytext NOT NULL,
  `special` tinyint(4) NOT NULL DEFAULT '0',
  `affiche` tinyint(1) NOT NULL DEFAULT '0',
  `lastMaskName` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`tid`),
  KEY `rTime` (`rTime`),
  KEY `replycount` (`replycount`),
  KEY `top` (`top`),
  KEY `tclass` (`tclass`),
  KEY `class` (`tid`),
  KEY `all` (`isdel`,`rTime`),
  KEY `dist` (`isdel`,`dist`,`rTime`),
  KEY `del` (`isdel`),
  KEY `cTime` (`cTime`),
  KEY `list` (`tagid`,`isdel`,`top`,`cTime`),
  KEY `tagid` (`tagid`),
  KEY `reply` (`tagid`,`tid`,`replycount`,`cTime`),
  KEY `uid` (`uid`),
  KEY `lastreuid` (`lastreuid`),
  KEY `maskId` (`maskId`),
  KEY `maskName` (`maskName`),
  KEY `maskNameDist` (`maskName`,`dist`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_topic
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_forum_user_group`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_user_group`;
CREATE TABLE `ts_forum_user_group` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `canDel` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_user_group
-- ----------------------------
INSERT INTO ts_forum_user_group VALUES ('1', '注册用户', '0', '0');
INSERT INTO ts_forum_user_group VALUES ('2', '版主', '1', '0');

-- ----------------------------
-- Table structure for `ts_forum_user_rule`
-- ----------------------------
DROP TABLE IF EXISTS `ts_forum_user_rule`;
CREATE TABLE `ts_forum_user_rule` (
  `usergid` smallint(8) NOT NULL,
  `fid` smallint(8) NOT NULL,
  `allow_post` tinyint(1) NOT NULL,
  `allow_post_attach` tinyint(1) NOT NULL,
  `allow_read` tinyint(1) NOT NULL,
  `allow_browser` tinyint(1) NOT NULL,
  `allow_read_attach` tinyint(1) NOT NULL,
  `allow_read_image` tinyint(1) NOT NULL,
  `allow_reply` tinyint(1) NOT NULL,
  `allow_edite` tinyint(1) NOT NULL,
  `allow_search` tinyint(1) NOT NULL,
  `allow_delete` tinyint(1) NOT NULL,
  `allow_post_special_thread` tinytext NOT NULL,
  `allow_edit_special_thread` tinytext NOT NULL,
  `allow_post_hide` tinyint(1) NOT NULL DEFAULT '0',
  `allow_read_hide` tinyint(1) NOT NULL,
  `allow_post_close` tinyint(1) NOT NULL DEFAULT '0',
  `attach_need_auditing` tinyint(1) NOT NULL,
  `allow_post_vote` tinyint(1) NOT NULL DEFAULT '0',
  `allow_post_noRely` tinyint(1) NOT NULL,
  KEY `tid_uid` (`usergid`,`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_forum_user_rule
-- ----------------------------


-- ----------------------------
-- Table structure for `ts_credit_setting`
-- ----------------------------
DELETE FROM `ts_credit_setting` WHERE `type` = 'forum';
INSERT INTO `ts_credit_setting` (`id`, `name`, `alias`, `type`, `info`, `score`, `experience`) 
VALUES
  ('', 'forum_post', '发主题', 'forum', '{action}{sign}了{score}{typecn}', '5', '5'),
  ('','forum_commend', '帖子推荐', 'forum', '{action}{sign}了{score}{typecn}', '10', '10'),
  ('','forum_delete_post', '删除帖子', 'forum', '{action}{sign}了{score}{typecn}', '-10', '-10'),
  ('','forum_edit', '编辑帖子', 'forum', '{action}{sign}了{score}{typecn}', '-10', '-10'),
  ('','forum_dist', '帖子设置精华', 'forum', '{action}{sign}了{score}{typecn}', '50', '50'),
  ('','forum_undist', '帖子撤销精华', 'forum', '{action}{sign}了{score}{typecn}', '-50', '-50'),
  ('','forum_reply', '帖子回复', 'forum', '{action}{sign}了{score}{typecn}', '1', '1'),
  ('','forum_delete_reply', '删除帖子回复', 'forum', '{action}{sign}了{score}{typecn}', '-1', '-1'),
  ('','forum_top', '帖子置顶', 'forum', '{action}{sign}了{score}{typecn}', '10', '10');
-- ----------------------------
-- Records of ts_credit_setting
-- ----------------------------

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) 
VALUES 
    (0,'forum','version_number','s:5:"33566";','2012-07-12 00:00:00');