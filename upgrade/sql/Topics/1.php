<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Topics` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `time` int(10) unsigned NOT NULL,
  `author` varchar(23) character set latin1 collate latin1_spanish_ci NOT NULL,
  `author_ip` varchar(100) character set latin1 NOT NULL,
  `replies` int(10) NOT NULL,
  `last_post` int(10) NOT NULL,
  `visits` int(10) NOT NULL default '0',
  `headline` varchar(100) character set latin1 NOT NULL,
  `body` text character set latin1 NOT NULL,
  `edit_time` int(10) default NULL,
  `edit_mod` tinyint(1) default NULL,
  PRIMARY KEY  (`id`),
  KEY `author` (`author`),
  KEY `author_ip` (`author_ip`),
  KEY `last_post` (`last_post`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

DB::SetTableRevision("Topics",1);
