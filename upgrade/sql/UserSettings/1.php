<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}UserSettings` (
  `uid` varchar(23) character set latin1 NOT NULL,
  `memorable_name` varchar(100) character set latin1 NOT NULL,
  `memorable_password` varchar(100) character set latin1 NOT NULL,
  `email` varchar(100) character set latin1 NOT NULL,
  `spoiler_mode` tinyint(1) NOT NULL default '0',
  `snippet_length` smallint(3) NOT NULL default '80',
  `topics_mode` tinyint(1) NOT NULL,
  `ostrich_mode` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `memorable_name` (`memorable_name`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
DB::SetTableRevision("UserSettings",1);
