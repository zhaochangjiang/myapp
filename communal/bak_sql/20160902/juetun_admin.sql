/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50612
Source Host           : localhost:3306
Source Database       : juetun_admin

Target Server Type    : MYSQL
Target Server Version : 50612
File Encoding         : 65001

Date: 2016-09-02 21:11:14
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
INSERT INTO `admin_group` VALUES ('1', '超级管理组', 'no', '3');
INSERT INTO `admin_group` VALUES ('3', '客服组', 'no', '0');

-- ----------------------------
-- Table structure for `admin_groupuser`
-- ----------------------------
DROP TABLE IF EXISTS `admin_groupuser`;
CREATE TABLE `admin_groupuser` (
  `admin_userid` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) NOT NULL,
  UNIQUE KEY `admin_userid` (`admin_userid`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_groupuser
-- ----------------------------
INSERT INTO `admin_groupuser` VALUES ('2', '3');

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
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COMMENT='权限表';

-- ----------------------------
-- Records of admin_permit
-- ----------------------------
INSERT INTO `admin_permit` VALUES ('2', '系统', 'system', '', '', '0', '9000000', 'fa fa-gear');
INSERT INTO `admin_permit` VALUES ('3', '数据', 'data', '', '', '0', '2000000', 'fa fa-file-text');
INSERT INTO `admin_permit` VALUES ('4', '后台用户', 'permit', 'user', 'list', '7', '0', '');
INSERT INTO `admin_permit` VALUES ('5', '系统开关', '', '', '', '2', '9800000', 'fa-tachometer');
INSERT INTO `admin_permit` VALUES ('6', '配置', '', '', '', '2', '9900000', '');
INSERT INTO `admin_permit` VALUES ('7', '管理员', '', '', '', '2', '9700000', 'fa-key');
INSERT INTO `admin_permit` VALUES ('8', '权限组', 'permit', 'group', 'list', '7', '0', '');
INSERT INTO `admin_permit` VALUES ('9', '权限组编辑', 'permit', 'data', 'edit', '12', '0', '');
INSERT INTO `admin_permit` VALUES ('10', '权限组编辑表单提交', 'permit', 'data', 'iframeGroupPermit', '9', '0', '');
INSERT INTO `admin_permit` VALUES ('12', '权限配置', 'permit', 'data', 'list', '6', '0', '');
INSERT INTO `admin_permit` VALUES ('13', '主页', 'dashboard', '', '', '0', '1000000', 'fa fa-home');
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
INSERT INTO `admin_permit` VALUES ('33', '图片类型', 'system', 'PictureCategory', 'list', '6', '0', '');
INSERT INTO `admin_permit` VALUES ('34', '编辑', 'system', 'pictureCategory', 'edit', '33', '0', '');
INSERT INTO `admin_permit` VALUES ('35', '删除', 'system', 'pictureCategory', 'delete', '33', '0', '');
INSERT INTO `admin_permit` VALUES ('36', '类型', 'data', 'category', 'list', '26', '0', '');
INSERT INTO `admin_permit` VALUES ('37', '删除', 'data', 'category', 'delete', '36', '0', '');
INSERT INTO `admin_permit` VALUES ('38', '编辑', 'data', 'category', 'edit', '36', '0', '');
INSERT INTO `admin_permit` VALUES ('39', '自动脚本', '', '', '', '2', '10000000', '');
INSERT INTO `admin_permit` VALUES ('40', '生成', 'automatically', 'create', 'index', '39', '0', '');

-- ----------------------------
-- Table structure for `admin_permitgroup`
-- ----------------------------
DROP TABLE IF EXISTS `admin_permitgroup`;
CREATE TABLE `admin_permitgroup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permit_id` int(10) NOT NULL,
  `group_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permit_idgroup_id` (`permit_id`,`group_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_permitgroup
-- ----------------------------
INSERT INTO `admin_permitgroup` VALUES ('73', '3', '1');
INSERT INTO `admin_permitgroup` VALUES ('85', '3', '3');
INSERT INTO `admin_permitgroup` VALUES ('76', '13', '3');
INSERT INTO `admin_permitgroup` VALUES ('65', '14', '1');
INSERT INTO `admin_permitgroup` VALUES ('77', '14', '3');
INSERT INTO `admin_permitgroup` VALUES ('66', '26', '1');
INSERT INTO `admin_permitgroup` VALUES ('78', '26', '3');
INSERT INTO `admin_permitgroup` VALUES ('67', '27', '1');
INSERT INTO `admin_permitgroup` VALUES ('79', '27', '3');
INSERT INTO `admin_permitgroup` VALUES ('69', '28', '1');
INSERT INTO `admin_permitgroup` VALUES ('81', '28', '3');
INSERT INTO `admin_permitgroup` VALUES ('70', '29', '1');
INSERT INTO `admin_permitgroup` VALUES ('82', '29', '3');
INSERT INTO `admin_permitgroup` VALUES ('68', '30', '1');
INSERT INTO `admin_permitgroup` VALUES ('80', '30', '3');
INSERT INTO `admin_permitgroup` VALUES ('71', '31', '1');
INSERT INTO `admin_permitgroup` VALUES ('83', '31', '3');
INSERT INTO `admin_permitgroup` VALUES ('72', '32', '1');
INSERT INTO `admin_permitgroup` VALUES ('84', '32', '3');

-- ----------------------------
-- Table structure for `admin_picture_category`
-- ----------------------------
DROP TABLE IF EXISTS `admin_picture_category`;
CREATE TABLE `admin_picture_category` (
  `picure_category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `picure_categoryname` varchar(30) NOT NULL COMMENT '图片类型名',
  `picure_categorykey` varchar(30) NOT NULL COMMENT '图片类型区分的KEY',
  `picure_savepath` varchar(100) NOT NULL COMMENT '图片保存路径',
  PRIMARY KEY (`picure_category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='本站系统图片类型表';

-- ----------------------------
-- Records of admin_picture_category
-- ----------------------------
INSERT INTO `admin_picture_category` VALUES ('1', '食材图片', 'ingredient', 'ingredient');
INSERT INTO `admin_picture_category` VALUES ('2', '食谱', 'recipe', 'recipe');

-- ----------------------------
-- Table structure for `admin_serverlist`
-- ----------------------------
DROP TABLE IF EXISTS `admin_serverlist`;
CREATE TABLE `admin_serverlist` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_addr` varchar(60) NOT NULL,
  `machine_room` varchar(30) NOT NULL COMMENT '机房',
  `unique_key` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_key` (`unique_key`),
  UNIQUE KEY `ip_addr` (`ip_addr`,`machine_room`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_serverlist
-- ----------------------------
INSERT INTO `admin_serverlist` VALUES ('3', '127.0.0.1', 'default', 'smqf');

-- ----------------------------
-- Table structure for `admin_user`
-- ----------------------------
DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user` (
  `uid` int(10) unsigned NOT NULL,
  `name` varchar(30) NOT NULL,
  `super_admin` enum('yes','no') NOT NULL DEFAULT 'no' COMMENT '是否为超级管理员',
  `isdel` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_user
-- ----------------------------
INSERT INTO `admin_user` VALUES ('1', '超级管理员1', 'no', 'no');
INSERT INTO `admin_user` VALUES ('2', '客服一', 'yes', 'no');
