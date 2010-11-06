<?php

DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Bulletins` (
  `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `time` INT(11) NOT NULL, 
  `author` varchar(23) NOT NULL,
  `body` TEXT NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

DB::SetTableRevision('Bulletins',1);
