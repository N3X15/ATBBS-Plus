<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Activity` (
  `uid` varchar(23) NOT NULL,
  `time` int(10) NOT NULL,
  `action_name` varchar(60) NOT NULL,
  `action_id` int(10) NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");


DB::SetTableRevision('Activity',1);
