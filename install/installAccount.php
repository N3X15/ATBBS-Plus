<?php
date_default_timezone_set('UTC');
header('Content-Type: text/html; charset=UTF-8');
session_cache_limiter('nocache');
session_name('SID');
session_start();

define('INSTALLER',true);
define('INSTALLING',true);

if(file_exists('.LOCK'))
	die('Installer is locked.  Remove the .LOCK file.');

if(!file_exists('config.php')) die('Please complete step 1 first.');
if($_SESSION['allowNewAdminAcct']!=true) die('Creating new accounts disabled.');

require_once("config.php");

// Patch to get shit working on Dreamhost/GoDaddy.  Also no extra shit to download.
set_include_path(get_include_path() . PATH_SEPARATOR . '../includes/3rdParty/');

include('adodb/adodb.inc.php');  
include("adodb/adodb-exceptions.inc.php"); 
include('Savant3.php');

require_once('../includes/db.php');
require_once('../includes/input.php');
require_once('../includes/output.php');
require_once('../includes/header.php');
require_once('../includes/functions.php');
require_once('../includes/user.php');

// Connect to the database.
Output::PrepSV3();
DB::Connect();
if(!DB::$rdb)
	die('Database connection failure.');

$User=new User();

$cfg=file_get_contents('config.php');
$cfg=str_replace('%%ADMIN_ID%%',$User->ID,$cfg);
file_put_contents('../includes/config.php',$cfg);

file_put_contents('.LOCK','.LOCK');

Output::Redirect('/','Account created.');


