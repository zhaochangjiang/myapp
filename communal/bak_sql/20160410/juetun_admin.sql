/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50612
Source Host           : localhost:3306
Source Database       : juetun_admin

Target Server Type    : MYSQL
Target Server Version : 50612
File Encoding         : 65001

Date: 2016-04-10 22:07:35
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `admin_group`
-- ----------------------------
DROP TABLE IF EXISTS `admin_group`;
CREATE TABLE `admin_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL COMMENT '权限组',
  `super_admin` enum('yes','no') NOT NULL DEFAULT 'no' COMMENT ' 是否为超级管理员组',
  `up_groupid` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_group
-- ----------------------------
INSERT INTO `admin_group` VALUES ('1', '超级管理组', 'yes', '0');
INSERT INTO `admin_group` VALUES ('3', '客服组', 'no', '0');

-- ----------------------------
-- Table structure for `admin_grouppermit`
-- ----------------------------
DROP TABLE IF EXISTS `admin_grouppermit`;
CREATE TABLE `admin_grouppermit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permit_id` int(10) NOT NULL,
  `group_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_grouppermit
-- ----------------------------
INSERT INTO `admin_grouppermit` VALUES ('30', '3', '3');

-- ----------------------------
-- Table structure for `admin_groupuser`
-- ----------------------------
DROP TABLE IF EXISTS `admin_groupuser`;
CREATE TABLE `admin_groupuser` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_userid` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_groupuser
-- ----------------------------

-- ----------------------------
-- Table structure for `admin_permit`
-- ----------------------------
DROP TABLE IF EXISTS `admin_permit`;
CREATE TABLE `admin_permit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `module` varchar(30) NOT NULL,
  `controller` varchar(30) NOT NULL,
  `action` varchar(30) NOT NULL,
  `uppermit_id` int(10) NOT NULL,
  `obyid` int(10) NOT NULL,
  `csscode` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COMMENT='权限表';

-- ----------------------------
-- Records of admin_permit
-- ----------------------------
INSERT INTO `admin_permit` VALUES ('2', '系统', 'system', '', '', '0', '0', 'fa fa-gear');
INSERT INTO `admin_permit` VALUES ('3', '数据', 'data', '', '', '0', '0', 'fa fa-file-text');
INSERT INTO `admin_permit` VALUES ('4', '后台用户', 'permit', 'user', 'list', '7', '0', '');
INSERT INTO `admin_permit` VALUES ('5', '系统开关', '', '', '', '2', '0', 'fa-tachometer');
INSERT INTO `admin_permit` VALUES ('6', '配置', '', '', '', '2', '0', '');
INSERT INTO `admin_permit` VALUES ('7', '权限设置', '', '', '', '2', '0', 'fa-key');
INSERT INTO `admin_permit` VALUES ('8', '权限组', 'permit', 'group', 'list', '7', '0', '');
INSERT INTO `admin_permit` VALUES ('9', '权限组编辑', 'permit', 'data', 'edit', '12', '0', '');
INSERT INTO `admin_permit` VALUES ('10', '权限组编辑表单提交', 'permit', 'data', 'iframeGroupPermit', '9', '0', '');
INSERT INTO `admin_permit` VALUES ('12', '权限配置', 'permit', 'data', 'list', '6', '0', '');
INSERT INTO `admin_permit` VALUES ('13', '主页', 'dashboard', '', '', '0', '0', 'fa fa-home');
INSERT INTO `admin_permit` VALUES ('14', '用户', '', '', '', '3', '0', 'fa fa-user');
INSERT INTO `admin_permit` VALUES ('15', '网站开关', 'system', 'switch', 'website', '5', '0', '');
INSERT INTO `admin_permit` VALUES ('16', '权限组编辑', 'permit', 'group', 'edit', '8', '0', '');
INSERT INTO `admin_permit` VALUES ('17', '权限组用户', 'permit', 'group', 'groupuser', '8', '0', '');
INSERT INTO `admin_permit` VALUES ('18', '删除', 'permit', 'data', 'delete', '12', '0', '');
INSERT INTO `admin_permit` VALUES ('19', '删除', 'permit', 'group', 'delete', '8', '0', '');
INSERT INTO `admin_permit` VALUES ('20', '删除权限组用户', 'permit', 'group', 'deletegroupuser', '17', '0', '');
INSERT INTO `admin_permit` VALUES ('21', '删除', 'permit', 'user', 'delete', '4', '0', '');
INSERT INTO `admin_permit` VALUES ('22', '编辑', 'permit', 'user', 'edit', '4', '0', '');
INSERT INTO `admin_permit` VALUES ('23', '设置权限', 'permit', 'group', 'setpermit', '8', '0', '');
INSERT INTO `admin_permit` VALUES ('24', '开启(关闭)权限', 'permit', 'group', 'ajaxsetpermit', '23', '0', '');
INSERT INTO `admin_permit` VALUES ('25', '批量开启(关闭)权限', 'permit', 'group', 'ajaxsetbatchpermit', '23', '0', '');
INSERT INTO `admin_permit` VALUES ('26', '基础数据', '', '', '', '3', '0', '');
INSERT INTO `admin_permit` VALUES ('27', '食材', 'data', 'ingredient', 'list', '26', '0', '');
INSERT INTO `admin_permit` VALUES ('28', ' 编辑', 'data', 'ingredient', 'edit', '27', '0', '');
INSERT INTO `admin_permit` VALUES ('29', '删除', 'data', 'ingredient', 'delete', '27', '0', '');
INSERT INTO `admin_permit` VALUES ('30', '图片', 'data', 'picture', 'list', '26', '0', '');
INSERT INTO `admin_permit` VALUES ('31', '编辑', 'data', 'picture', 'edit', '30', '0', '');
INSERT INTO `admin_permit` VALUES ('32', '删除', 'data', 'picture', 'delete', '30', '0', '');

-- ----------------------------
-- Table structure for `admin_picture_category`
-- ----------------------------
DROP TABLE IF EXISTS `admin_picture_category`;
CREATE TABLE `admin_picture_category` (
  `picure_category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `picure_type_name` varchar(30) NOT NULL COMMENT '图片类型名',
  PRIMARY KEY (`picure_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='本站系统图片类型表';

-- ----------------------------
-- Records of admin_picture_category
-- ----------------------------

-- ----------------------------
-- Table structure for `admin_user`
-- ----------------------------
DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user` (
  `uid` int(10) unsigned NOT NULL,
  `name` varchar(30) NOT NULL,
  `isdel` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_user
-- ----------------------------
INSERT INTO `admin_user` VALUES ('1', '超级管理员', 'no');
INSERT INTO `admin_user` VALUES ('2', '客服一', 'no');
