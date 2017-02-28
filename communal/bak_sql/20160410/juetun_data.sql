/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50612
Source Host           : localhost:3306
Source Database       : juetun_data

Target Server Type    : MYSQL
Target Server Version : 50612
File Encoding         : 65001

Date: 2016-04-10 22:07:41
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `data_ingredient`
-- ----------------------------
DROP TABLE IF EXISTS `data_ingredient`;
CREATE TABLE `data_ingredient` (
  `ingredient_id` int(10) NOT NULL,
  `ingredient_thumbnail` int(11) NOT NULL COMMENT '缩略图ID',
  `ingredient_name` varchar(30) NOT NULL COMMENT '食材名',
  `ingredient_peculiarity` varchar(150) NOT NULL COMMENT '食材特点',
  `ingredient_keyword` varchar(150) NOT NULL COMMENT '食材描述',
  `ingredient_desc` varchar(150) NOT NULL COMMENT '食材简单描述',
  `ingredient_images` text NOT NULL COMMENT '图片ID数组用“,”分割',
  `update_time` datetime NOT NULL COMMENT ' 更新时刻',
  `ingredient_description` text NOT NULL COMMENT ' 食材详细描述',
  PRIMARY KEY (`ingredient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='食材表';

-- ----------------------------
-- Records of data_ingredient
-- ----------------------------

-- ----------------------------
-- Table structure for `data_picture`
-- ----------------------------
DROP TABLE IF EXISTS `data_picture`;
CREATE TABLE `data_picture` (
  `picture_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(15) NOT NULL,
  `del_flag` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT ' 是否删除(1:“已删除”,0:"未删除")',
  `picture_category_id` int(10) NOT NULL,
  `upload_time` int(10) unsigned NOT NULL,
  `suffix` varchar(5) NOT NULL COMMENT '文件后缀名',
  PRIMARY KEY (`picture_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of data_picture
-- ----------------------------
