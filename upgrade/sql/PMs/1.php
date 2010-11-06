<?php

DB::Execute("CREATE TABLE IF NOT EXISTS {P}PMs (
	pmID INT(11) PRIMARY KEY AUTO_INCREMENT,
	pmThread INT(11),
	pmTitle VARCHAR(100),
	pmFrom VARCHAR(23),
	pmTo VARCHAR(23),
	pmBody TEXT,
	pmDateSent INT(11),
	pmFlags INT(11) DEFAULT 0
) ENGINE=MyISAM CHARSET=utf8");

DB::SetTableRevision('PMs',1);
