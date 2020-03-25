/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : study_sns

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2020-02-25 17:40:21
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for article
-- ----------------------------
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `art_id` int(11) NOT NULL,
  `art_user_id` int(11) NOT NULL,
  `art_title` varchar(255) NOT NULL,
  `art_content` varchar(255) NOT NULL,
  `art_board_id` int(11) NOT NULL,
  `art_time` datetime NOT NULL,
  `art_count` varchar(255) NOT NULL,
  `art_view` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`art_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of article
-- ----------------------------

-- ----------------------------
-- Table structure for board
-- ----------------------------
DROP TABLE IF EXISTS `board`;
CREATE TABLE `board` (
  `board_id` int(11) NOT NULL,
  `borard_name` varchar(255) NOT NULL,
  `board_user_id` int(11) DEFAULT NULL,
  `board_count` int(255) DEFAULT NULL,
  PRIMARY KEY (`board_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of board
-- ----------------------------

-- ----------------------------
-- Table structure for comment
-- ----------------------------
DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `com_id` int(11) NOT NULL,
  `com_user_id` int(11) NOT NULL,
  `com_content` varchar(255) NOT NULL,
  `com_art_id` int(11) NOT NULL,
  `com_count` varchar(255) NOT NULL,
  `com_time` datetime NOT NULL,
  `re_com_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`com_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of comment
-- ----------------------------

-- ----------------------------
-- Table structure for file
-- ----------------------------
DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `file_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_user_id` int(11) NOT NULL,
  `file_cost` int(11) NOT NULL,
  `file_count` int(255) DEFAULT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of file
-- ----------------------------

-- ----------------------------
-- Table structure for follow
-- ----------------------------
DROP TABLE IF EXISTS `follow`;
CREATE TABLE `follow` (
  `fo_id` int(11) NOT NULL,
  `fo_user_id` int(11) NOT NULL,
  `fo_follower_id` int(11) NOT NULL,
  PRIMARY KEY (`fo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of follow
-- ----------------------------

-- ----------------------------
-- Table structure for mail
-- ----------------------------
DROP TABLE IF EXISTS `mail`;
CREATE TABLE `mail` (
  `mail_id` int(11) NOT NULL,
  `mail_user_id` int(11) NOT NULL,
  `mail_receiver_id` int(11) NOT NULL,
  `mail_content` varchar(255) NOT NULL,
  `mail_status` int(11) NOT NULL,
  `mail_time` datetime NOT NULL,
  PRIMARY KEY (`mail_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of mail
-- ----------------------------

-- ----------------------------
-- Table structure for sign
-- ----------------------------
DROP TABLE IF EXISTS `sign`;
CREATE TABLE `sign` (
  `sign_id` int(11) NOT NULL,
  `sign_user_id` int(11) NOT NULL,
  `user_mood` varchar(255) NOT NULL,
  `sign_content` varchar(255) DEFAULT NULL,
  `sign_time` datetime NOT NULL,
  PRIMARY KEY (`sign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of sign
-- ----------------------------

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_pwd` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_regtime` datetime DEFAULT NULL,
  `user_lasttime` datetime DEFAULT NULL,
  `user_icon` varchar(255) DEFAULT NULL,
  `user_sex` int(11) DEFAULT NULL,
  `user_point` varchar(255) DEFAULT NULL,
  `user_money` varchar(255) DEFAULT NULL,
  `user_level` varchar(255) DEFAULT NULL,
  `user_grant` varchar(255) DEFAULT NULL,
  `user_mood` varchar(255) DEFAULT NULL,
  `user_days` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of user
-- ----------------------------
