<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Trash` (
  `uid` varchar(23) NOT NULL,
  `time` int(10) NOT NULL,
  `headline` varchar(100) NOT NULL,
  `body` text NOT NULL,
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
DB::SetTableRevision("Trash",1);
