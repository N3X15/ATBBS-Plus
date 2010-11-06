<?php
DB::Execute("CREATE TABLE IF NOT EXISTS `{P}Filters` (
	`filID` INT(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`filText` TEXT NOT NULL,
	`filReason` TEXT NOT NULL,
	`filPunishType` INT(2) NOT NULL,
	`filPunishDuration` INT(11) NOT NULL,
	`filReplacement` TEXT NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
DB::SetTableRevision("Filters",1);
