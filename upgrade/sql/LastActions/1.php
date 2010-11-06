<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}LastActions` (
  `feature` varchar(30) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`feature`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
DB::SetTableRevision("LastActions",1);
