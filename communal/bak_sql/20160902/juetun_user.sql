/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50612
Source Host           : localhost:3306
Source Database       : juetun_user

Target Server Type    : MYSQL
Target Server Version : 50612
File Encoding         : 65001

Date: 2016-09-02 21:11:29
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `user_main`
-- ----------------------------
DROP TABLE IF EXISTS `user_main`;
CREATE TABLE `user_main` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL COMMENT ' 用户名',
  `name` varchar(30) NOT NULL COMMENT ' 姓名',
  `email` varchar(50) NOT NULL COMMENT ' 邮箱',
  `mobile` varchar(11) NOT NULL COMMENT ' 手机号',
  `password` varchar(50) NOT NULL,
  `avater` int(10) unsigned NOT NULL COMMENT '头像ID',
  `gender` enum('male','female') NOT NULL DEFAULT 'male',
  `step` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '会员等级',
  `score` int(11) unsigned NOT NULL COMMENT ' 积分',
  `gaode_mapx` decimal(10,7) NOT NULL COMMENT '高德地图坐标经度',
  `gaode_mapy` decimal(10,7) NOT NULL COMMENT '高德地图坐标纬度',
  `have_admin_permit` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT ' 是否有客服后台权限0：没有，1:有',
  `flag_del` enum('yes','no') NOT NULL DEFAULT 'no' COMMENT '是否删除,''yes'':是，''no'':否',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_main
-- ----------------------------
INSERT INTO `user_main` VALUES ('1', 'admin', '', '', '', '3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d', '0', 'male', '0', '0', '0.0000000', '0.0000000', '1', 'no');
INSERT INTO `user_main` VALUES ('2', 'clienttest', '', '', '', '3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d', '0', 'male', '0', '0', '0.0000000', '0.0000000', '1', 'no');
