SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `yunzhi_teacher`
-- ----------------------------
DROP TABLE IF EXISTS `yunzhi_teacher`;
CREATE TABLE `yunzhi_teacher` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT '' COMMENT '����',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0�У�1Ů',
  `username` varchar(16) NOT NULL COMMENT '�û���',
  `email` varchar(30) DEFAULT '' COMMENT '����',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '����ʱ��',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '����ʱ��',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `yunzhi_teacher`
-- ----------------------------
BEGIN;
INSERT INTO `yunzhi_teacher` VALUES ('1', '����', '0', 'zhangsan', 'zhangsan@mail.com', '123123', '123213'), ('2', '����', '0', 'lisi', 'lisi@yunzhi.club', '123213', '1232');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;