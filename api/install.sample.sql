CREATE DATABASE IF NOT EXISTS `test_sports_meeting` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `test_sports_meeting`;

CREATE TABLE IF NOT EXISTS `sports_meeting_danmaku` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT '序号',
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '弹幕发射时间',
  `text` VARCHAR(255) NOT NULL COMMENT '弹幕内容',
  `position` INT NOT NULL DEFAULT '0' COMMENT '弹幕位置',
  `color` INT NOT NULL DEFAULT '16777215' COMMENT '弹幕颜色',
  `size` INT NOT NULL DEFAULT '1' COMMENT '弹幕大小',
  `cookie` VARCHAR(40) NULL,
  `ip` VARCHAR(40) NULL COMMENT '来源ip',
  PRIMARY KEY (`id`)
) ENGINE = MyISAM CHARSET = utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `sports_meeting_vote_detail` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT '序号',
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '弹幕发射时间',
  `votefor` INT NOT NULL COMMENT '投票书院id',
  `cookie` VARCHAR(40) NULL,
  `ip` VARCHAR(40) NULL COMMENT '来源ip',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARSET = utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `sports_meeting_vote_count` (
  `id` INT NOT NULL COMMENT '书院id',
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `count` INT NOT NULL DEFAULT '0' COMMENT '书院票数',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;

INSERT INTO `sports_meeting_vote_count` (`id`, `count`) VALUES
  ('0', '0'),
  ('1', '0'),
  ('2', '0'),
  ('3', '0'),
  ('4', '0'),
  ('5', '0'),
  ('6', '0'),
  ('7', '0');
