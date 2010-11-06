<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Replies` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) NOT NULL,
  `poster_number` int(10) NOT NULL,
  `author` varchar(23) character set latin1 NOT NULL,
  `author_ip` varchar(100) character set latin1 NOT NULL,
  `time` int(10) NOT NULL,
  `body` text character set latin1 NOT NULL,
  `edit_time` int(10) default NULL,
  `edit_mod` tinyint(1) default NULL,
  PRIMARY KEY  (`id`),
  KEY `parent_id` (`parent_id`,`author`,`author_ip`),
  KEY `letter` (`poster_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
DB::SetTableRevision("Replies",1);
