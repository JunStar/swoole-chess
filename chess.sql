/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50624
 Source Host           : localhost
 Source Database       : chess

 Target Server Type    : MySQL
 Target Server Version : 50624
 File Encoding         : utf-8

 Date: 05/09/2016 14:41:43 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `chess`
-- ----------------------------
DROP TABLE IF EXISTS `chess`;
CREATE TABLE `chess` (
  `chess_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `red_user_id` int(10) unsigned NOT NULL COMMENT '红方用户ID',
  `black_user_id` int(10) unsigned NOT NULL COMMENT '黑方用户ID',
  `victory` varchar(255) DEFAULT NULL COMMENT '胜利方',
  PRIMARY KEY (`chess_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `chess_moves`
-- ----------------------------
DROP TABLE IF EXISTS `chess_moves`;
CREATE TABLE `chess_moves` (
  `chess_moves_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chess_id` int(10) unsigned NOT NULL COMMENT '所属对局ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '下这步棋的用户',
  `camp` varchar(255) NOT NULL COMMENT '下这步棋的用户所在的阵营，red或black',
  `move` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '这步棋的中文描述，比如：炮二平五',
  `red_pace` varchar(255) NOT NULL COMMENT '红棋在下方时，棋子走动的坐标',
  `black_pace` varchar(255) NOT NULL COMMENT '黑棋在下方时，棋子走动的坐标',
  `red_map` text NOT NULL COMMENT '红方在下方的所有子力布局',
  `black_map` text NOT NULL COMMENT '黑方在下方的所有子力布局',
  PRIMARY KEY (`chess_moves_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
