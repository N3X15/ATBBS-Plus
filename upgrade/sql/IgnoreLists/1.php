<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}IgnoreLists` (
  `uid` varchar(23) NOT NULL,
  `ignored_phrases` text NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
DB::SetTableRevision("IgnoreLists",1);
