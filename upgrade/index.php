<?php
/**
* Perform database updates
*/
define('IN_ATBBS',1);
define('UPGRADER',1);
if(!@include_once('../includes/config.php'))
{
	header('Refresh:5,../install/');
	?>Unable to find configuration file;  Redirecting to ATBBS Installer in 5 sec.<?
	die('');
}


// Patch to get shit working on Dreamhost/GoDaddy.  Also no extra shit to download.
set_include_path(get_include_path() . PATH_SEPARATOR . '../includes/3rdParty/');
include('adodb/adodb.inc.php');
include('Savant3.php');

require_once(LIBD.'input.php');
require_once(LIBD.'output.php');
require_once(LIBD.'db.php');

DB::Connect();
if(!DB::NeedsUpgrade()) die('No upgrade required.');
include('upgradeheader.php');
require 'page1.php';
include('upgradefooter.php');
