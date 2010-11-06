<?php
/**
* Skin support
*/


define('FLAG_SPOILER',	4);	// 3rd bit
define('FLAG_TOPICS',	8);	// 4th bit
define('FLAG_OSTRICH',	16);	// 5th bit

$theme=THEME;

// Backup 
DB::Execute("DROP TABLE IF EXISTS BACKUP_{P}UserSettings");
DB::Execute("DROP TABLE IF EXISTS NEW_{P}UserSettings");

DB::Execute(
"CREATE TABLE NEW_{P}UserSettings
(
	usrID 	VARCHAR(23) NOT NULL PRIMARY KEY,
	usrName	VARCHAR(100) NOT NULL,
	usrPasshash VARCHAR(40) NOT NULL,
	usrFlags INT(11) NOT NULL DEFAULT 0,
	usrEmail VARCHAR(100) NOT NULL,
	usrSnipLen INT(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
");

// I'm really proud of this.  I don't know why, as it's very ugly.
// tl;dr Copies old shit into new shitbucket BUT:
// * Password is SHA-1 hashed with a UID salt
// * spoiler_mode and co are turned into flags
// * Fields are renamed
DB::Execute("
INSERT INTO NEW_{P}UserSettings
SELECT 
	uid as usrID, 
	memorable_name as usrName, 
	SHA1(CONCAT(uid,memorable_password)) as usrPasshash,
	( 0 | ( `spoiler_mode` *".FLAG_SPOILER." ) | ( `topics_mode` *".FLAG_TOPICS." ) | ( `ostrich_mode` *".FLAG_OSTRICH." ) ) AS usrFlags,
	email as usrEmail,
	snippet_length as usrSnipLen
FROM {P}UserSettings");

DB::Execute("RENAME TABLE {P}UserSettings TO BACKUP_{P}UserSettings");
DB::Execute("RENAME TABLE NEW_{P}UserSettings TO {P}UserSettings");

DB::SetTableRevision('UserSettings',2);
