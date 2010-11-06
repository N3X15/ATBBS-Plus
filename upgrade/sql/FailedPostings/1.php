<?php

DB::Execute("CREATE TABLE IF NOT EXISTS `{P}FailedPostings` (
  `uid` varchar(23) NOT NULL,
  `time` int(10) NOT NULL,
  `reason` text NOT NULL,
  `headline` varchar(100) NOT NULL,
  `body` text NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

DB::SetTableRevision('FailedPostings',1);
