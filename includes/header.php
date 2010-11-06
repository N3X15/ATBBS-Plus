<?php
/**
* Initialization-related stuff
* 
* Copyright (c) 2009-2010 ATBBS Contributors
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/

date_default_timezone_set('UTC');
header('Content-Type: text/html; charset=UTF-8');
session_cache_limiter('nocache');
session_name('SID');
session_start();

// Patch to get shit working on Dreamhost/GoDaddy.  Also no extra shit to download.
set_include_path(get_include_path() . PATH_SEPARATOR . 'includes/3rdParty/');

$main_menu = array
(
	'Hot'		=> '/hot_topics',
	'Topics' 	=> '/',
	'Bumps' 	=> '/bumps',
	'Replies' 	=> '/replies',
	'New topic' 	=> '/new_topic',
	'History' 	=> '/history',
	'Watchlist' 	=> '/watchlist',
	'Bulletins'	=> '/bulletins', // Too lazy to update the fucking .htaccess files...
	'Folks'		=> '/folks',
	'Search' 	=> '/search',
	'FAQ' 		=> '/FAQ',
	'Stuff' 	=> '/stuff',
);

if(!defined('INSTALLER'))
{
	if(!@include_once('includes/config.php'))
	{
		include('install/installheader.php');
?>
	<p>ATBBS needs to be installed.  Please click <a href="install/">here</a> to get started.</p>
<?
		include('install/installfooter.php');
		exit;
	}
	require_once('adodb/adodb.inc.php');
	require_once('Savant3.php');
	require_once('HTML/QuickForm2.php');

	require_once(LIBD.'functions.php');
	require_once(LIBD.'db.php');
	require_once(LIBD.'output.php');
	require_once(LIBD.'input.php');
	require_once(LIBD.'table.php');
	require_once(LIBD.'user.php');
	require_once(LIBD.'filters.php');
	require_once(LIBD.'moderation.php');

	require_once(LIBD.'reply.php');
	require_once(LIBD.'topic.php');
	require_once(LIBD.'pms.php');

	if(!defined('MOD_NAME'))
		define('MOD_NAME','Wiseguy');
	
	if(!defined('ROOT_ADMIN'))
		$_SESSION['notice']="<b>NOTICE TO ADMINISTRATOR:</b> Please add <code>define('ROOT_ADMIN','(Your UID)');</code> to includes/config.php ASAP.";

	Output::PrepSV3();
	if(!defined('ADODB_DRIVER'))
		die('Please finish <a href="/install">installing</a> ATBBS.');

	// Connect to the database.
	DB::Connect();

	if(DB::NeedsUpgrade() && !defined('UPGRADER'))
		Output::HardError('The database engine has determined that the database needs an upgrade.  Please visit <a href="/upgrade/">ATBBS Upgrader</a> to remedy the problem.');
	$User=new User();

	$moderator = $User->isMod();
	$administrator = $User->isAdmin();
	if(!defined('INSTALLER'))
	{
		// Start buffering shit for the template.
		ob_start(); 
	}

	Check4Ban();

	// Dashboard sidebar
	$sidebar=array(
		'User Toolbox' 		=> array(
			array('dashboard/',		'Dashboard',		'Your personal settings, including username and password.'),
			array('edit_ignore_list/',	'Ignore List',		'Edit your personal ignore list, to keep the bad thoughts out.'),
			array('trash_can/',		'Trash',		'Your deleted posts, in an easy-to-access list.')
		),
		'ID Toolbox'		=> array(
			array('restore_ID/',		'Restore ID',		'Similar to logging in.'),
			array('back_up_ID/',		'Back Up ID',		'Save your ID to a portable cardfile.'),
			array('recover_ID_by_email/',	'Email ID',		'Look up your ID by email address.'),
			array('drop_id/',		'Drop ID',		'Log out of '.SITE_TITLE.'.'),
		),
		'Statistics' 		=> array(
			array('statistics/',		'Main',			'General Statistics.'),
			array('failed_postings/',	'Failed Posts',		'See how users managed to fuck up their posts!'),
			array('date_and_time/',		'Date and Time',	'A cool little gadget.')
		)
	);

	if(isPowerUser())
	{
		$tbt=$User->Level.' Toolbox';
		$sidebar[$tbt]=array();
		if($User->isAdmin())
			$sidebar[$tbt][]=array('CMS',			'CMS',			'Edit pages \'n\' shit.');
		$sidebar[$tbt][]=array('controlpanel.php/read_appeals/',	'Appeals',		'Active appeals.');
		if($User->isAdmin() || ($User->isMod() && ALLOW_MODS_EXTERMINATE))
			$sidebar[$tbt][]=array('exterminate/',		'Exterminate',		'Nuke shitposts en masse.');
	// None of this shit is ready yet.
		if($User->isAdmin())
		{
			$sidebar[$tbt][]=array('controlpanel.php/powerusers/',			'User Rights',		'Assign/Revoke moderator/admin powers.');
	//		$sidebar[$tbt][]=array('controlpanel.php/logs/',			'Moderator Logs',	'Keep an eye on your assets.');
			$sidebar[$tbt][]=array('controlpanel.php/filters/',			'Shit Filters',		'Ban or modify on keywords.');
		}	
	}
	if($_GET['DBG'])
		DB::ToggleDebug();

	CheckPMs();
} // INSTALLER

