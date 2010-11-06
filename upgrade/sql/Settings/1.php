<?php

// Namespace: Board
// Category: Spamfilter
// Name: Enabled
//	board.spamfilter.enabled

DB::Execute("CREATE TABLE {P}Settings (
	setName 	VARCHAR(50) NOT NULL PRIMARY KEY,
	setCategory	VARCHAR(50) NOT NULL PRIMARY KEY,
	setNamespace	VARCHAR(20) NOT NULL PRIMARY KEY,
	setDescription	TEXT,
	setType		INT(2) NOT NULL,
	setDefault	TEXT,
	setValue	TEXT
) ENGINE=MyISAM CHARSET=utf8");

if(!defined(SITE_TITLE))
	define('SITE_TITLE','New ATBBS Board');


// ATBBS Core Settings
	'atbbs.core.boardname'	=>array(SET_TYPE_TEXT,	SITE_TITLE,	'',		'The name of your site to display at the top of the page.'),
	'atbbs.core.subtitle'	=>array(SET_TYPE_TEXT,	'Hot off the presses.',	'',	'Your site's subtitle.'),
	'atbbs.core.defcon'	=>array(SET_TYPE_EXT
);
