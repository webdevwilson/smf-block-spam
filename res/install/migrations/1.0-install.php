<?php

if (!defined('SMF'))
	die('something fishy going on');

db_query("CREATE TABLE IF NOT EXISTS `{$db_prefix}spam_messages` (
  `ID_MSG` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ID_TOPIC` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ID_BOARD` smallint(5) unsigned NOT NULL DEFAULT '0',
  `posterTime` int(10) unsigned NOT NULL DEFAULT '0',
  `ID_MEMBER` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ID_MSG_MODIFIED` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` tinytext NOT NULL,
  `posterName` tinytext NOT NULL,
  `posterEmail` tinytext NOT NULL,
  `posterIP` tinytext NOT NULL,
  `smileysEnabled` tinyint(4) NOT NULL DEFAULT '1',
  `modifiedTime` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedName` tinytext NOT NULL,
  `body` text NOT NULL,
  `icon` varchar(16) NOT NULL DEFAULT 'xx',
  `spam` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`ID_MSG`),
  UNIQUE KEY `topic` (`ID_TOPIC`,`ID_MSG`),
  UNIQUE KEY `ID_BOARD` (`ID_BOARD`,`ID_MSG`),
  UNIQUE KEY `ID_MEMBER` (`ID_MEMBER`,`ID_MSG`),
  KEY `ipIndex` (`posterIP`(15),`ID_TOPIC`),
  KEY `participation` (`ID_MEMBER`,`ID_TOPIC`),
  KEY `showPosts` (`ID_MEMBER`,`ID_BOARD`),
  KEY `ID_TOPIC` (`ID_TOPIC`)
)", __FILE__, __LINE__);

db_query("INSERT IGNORE INTO `{$db_prefix}settings` (`variable`, `value`) VALUES ('blockSpamCaughtMessages', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO `{$db_prefix}settings` (`variable`, `value`) VALUES ('blockSpamAkismetKey', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO `{$db_prefix}settings` (`variable`, `value`) VALUES ('blockSpamPostsThreshold', '10')", __FILE__, __LINE__);

?>