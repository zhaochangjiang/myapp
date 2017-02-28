/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50612
Source Host           : localhost:3306
Source Database       : juetun_data

Target Server Type    : MYSQL
Target Server Version : 50612
File Encoding         : 65001

Date: 2016-05-20 21:29:51
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
  `ingredient_foodalias` varchar(150) NOT NULL COMMENT '食材别名',
  `ingredient_keyword` varchar(150) NOT NULL COMMENT '食材关键词',
  `ingredient_desc` varchar(150) NOT NULL COMMENT '食材简单描述',
  `ingredient_images` text NOT NULL COMMENT '图片ID数组用“,”分割',
  `update_time` datetime NOT NULL COMMENT ' 更新时刻',
  `ingredient_description` text NOT NULL COMMENT ' 食材详细描述',
  PRIMARY KEY (`ingredient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='食材表';

-- ----------------------------
-- Records of data_ingredient
-- ----------------------------
INSERT INTO `data_ingredient` VALUES ('0', '0', '', '', '', '', '', '0000-00-00 00:00:00', '葱（学名：Allium fistulosum L.），为百合科葱属多年生草本植物。鳞茎单生，圆柱状，稀为基部膨大的卵状圆柱形；鳞茎外皮白色，稀淡红褐色，膜质至薄革质，不破裂。叶圆筒状，中空；花葶圆柱状，中空，中部以下膨大，向顶端渐狭；总苞膜质，伞形花序球状，多花，较疏散；花被片长6-8.5毫米，近卵形；花丝为花被片长度的1.5-2倍，锥形；子房倒卵状，腹缝线基部具不明显的蜜穴；花柱细长，伸出花被外。花果期4-7月。');

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
