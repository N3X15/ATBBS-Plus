<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Watchlists` (
  `uid` varchar(23) NOT NULL,
  `topic_id` int(10) NOT NULL,
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
DB::SetTableRevision("Watchlists",1);
