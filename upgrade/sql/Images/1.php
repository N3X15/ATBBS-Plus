<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Images` (
  `file_name` varchar(80) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `topic_id` int(10) unsigned DEFAULT NULL,
  `reply_id` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `reply_id` (`reply_id`),
  UNIQUE KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
DB::SetTableRevision("Images",1);
