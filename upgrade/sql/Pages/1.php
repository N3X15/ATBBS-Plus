<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Pages` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `url` varchar(100) NOT NULL,
  `page_title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;");
DB::SetTableRevision("Pages",1);
