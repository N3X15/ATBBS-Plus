<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Bans` (
  `uid` varchar(23) NOT NULL,
  `ip` varchar(39) NOT NULL,
  `expiry` int(11) NOT NULL,
  `flags` int(11) NOT NULL,
  `appeal` text NOT NULL,
  `reason` text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
DB::SetTableRevision("Bans",1);
