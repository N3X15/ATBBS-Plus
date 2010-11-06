<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Users` (
  `uid` varchar(23) character set latin1 NOT NULL,
  `password` varchar(32) character set latin1 NOT NULL,
  `first_seen` int(10) NOT NULL,
  `ip_address` varchar(100) character set latin1 NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `first_seen` (`first_seen`),
  KEY `ip_address` (`ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
DB::SetTableRevision("Users",1);
