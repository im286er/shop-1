/*
Navicat MySQL Data Transfer

Source Server         : 111111111111
Source Server Version : 50508
Source Host           : localhost:3306
Source Database       : auth_new

Target Server Type    : MYSQL
Target Server Version : 50508
File Encoding         : 65001

Date: 2016-07-17 21:30:00
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `db_admin`
-- ----------------------------
DROP TABLE IF EXISTS `db_admin`;
CREATE TABLE `db_admin` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `account` varchar(32) DEFAULT NULL COMMENT '管理员账号',
  `password` varchar(36) DEFAULT NULL COMMENT '管理员密码',
  `login_time` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `login_count` mediumint(8) NOT NULL COMMENT '登录次数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '账户状态，禁用为0   启用为1',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of db_admin
-- ----------------------------
INSERT INTO `db_admin` VALUES ('1', 'admin', '21232f297a57a5a743894a0e4a801fc3', '1468761924', '236', '1', '1437979578');
INSERT INTO `db_admin` VALUES ('2', 'kefu1', 'e10adc3949ba59abbe56e057f20f883e', '1468762016', '1', '1', '1468761999');

-- ----------------------------
-- Table structure for `db_auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `db_auth_group`;
CREATE TABLE `db_auth_group` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` char(80) NOT NULL DEFAULT '',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of db_auth_group
-- ----------------------------
INSERT INTO `db_auth_group` VALUES ('30', '超级管理组', '1', '1,2,4,3,7,8,9', '1445158837');
INSERT INTO `db_auth_group` VALUES ('31', '客服组', '1', '7,8,9', '1468761976');

-- ----------------------------
-- Table structure for `db_auth_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `db_auth_group_access`;
CREATE TABLE `db_auth_group_access` (
  `uid` smallint(5) unsigned NOT NULL,
  `group_id` smallint(5) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of db_auth_group_access
-- ----------------------------
INSERT INTO `db_auth_group_access` VALUES ('1', '30');
INSERT INTO `db_auth_group_access` VALUES ('2', '31');
INSERT INTO `db_auth_group_access` VALUES ('9', '46');

-- ----------------------------
-- Table structure for `db_auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `db_auth_rule`;
CREATE TABLE `db_auth_rule` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL DEFAULT '',
  `title` varchar(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `condition` char(100) NOT NULL DEFAULT '',
  `pid` smallint(5) NOT NULL COMMENT '父级ID',
  `sort` tinyint(4) NOT NULL DEFAULT '50' COMMENT '排序',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL,
  `remark` varchar(30) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of db_auth_rule
-- ----------------------------
INSERT INTO `db_auth_rule` VALUES ('1', 'Admin/index', '系统管理', '1', '1', '', '0', '51', '1444546437', '1460223059', null);
INSERT INTO `db_auth_rule` VALUES ('2', 'Admin/admin_list', '管理员', '1', '1', '', '1', '50', '1444582187', null, null);
INSERT INTO `db_auth_rule` VALUES ('4', 'Admin/auth_group', '用户组', '1', '1', '', '1', '50', '1445439984', null, null);
INSERT INTO `db_auth_rule` VALUES ('6', 'Admin/auth_rule', '权限及菜单', '1', '1', '', '1', '50', '1445439984', null, null);
INSERT INTO `db_auth_rule` VALUES ('3', 'Admin/admin_add', '添加管理员', '1', '1', '', '1', '50', '1463454851', null, null);
INSERT INTO `db_auth_rule` VALUES ('5', 'Admin/group_add', '添加用户组', '1', '1', '', '1', '50', '1463454885', null, null);
INSERT INTO `db_auth_rule` VALUES ('7', 'User/index', '客户管理', '1', '1', '', '0', '50', '1453261131', null, null);
INSERT INTO `db_auth_rule` VALUES ('8', 'User/user_list', '客户列表', '1', '1', '', '7', '50', '1453261153', null, null);
INSERT INTO `db_auth_rule` VALUES ('9', 'User/user_sms_list', '短信验证码', '1', '1', '', '7', '50', '1459413055', null, null);

-- ----------------------------
-- Table structure for `db_user`
-- ----------------------------
DROP TABLE IF EXISTS `db_user`;
CREATE TABLE `db_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '账号状态   1正常   0禁用',
  `update_time` int(11) DEFAULT NULL,
  `openid` varchar(40) DEFAULT NULL,
  `huifei_sum` int(11) NOT NULL DEFAULT '0' COMMENT '会费金额',
  `password` varchar(40) DEFAULT NULL COMMENT '密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of db_user
-- ----------------------------
INSERT INTO `db_user` VALUES ('23', '18654160150', '1461247485', '1', null, 'ozXRDt3OUsSieNiPFDMa7kDFjx9Q', '0', 'e10adc3949ba59abbe56e057f20f883e');

-- ----------------------------
-- Table structure for `db_user_sms`
-- ----------------------------
DROP TABLE IF EXISTS `db_user_sms`;
CREATE TABLE `db_user_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sms_code` int(7) DEFAULT NULL COMMENT '验证码',
  `create_time` int(11) DEFAULT NULL,
  `mobile` varchar(11) DEFAULT NULL COMMENT '发送的手机号',
  `send_result` text COMMENT '发送结果',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of db_user_sms
-- ----------------------------
INSERT INTO `db_user_sms` VALUES ('15', '1271', '1461230760', '18654160150', '\r\n\r\n\r\n<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><error>0</error><message></message></response>\r\n\r\n');
