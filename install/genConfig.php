<?php
/**
 * Main Config Generator
 *
 * This file has the main configuration in it.  Duh.
 */

if(!IN_ATBBS)
	die('');

	
function _trim(&$value,$key)
{
	$value = trim($value);
}
array_walk($values,"_trim");

$random=md5(time().$_SERVER['REMOTE_ADDR'].__FILE__.__LINE__.mt_rand()).mt_rand();
$time = time();

$cfg=<<<EOF
<?php
/**
 * Main Config
 *
 * This file has the main configuration in it.  Duh.
 */

/*/////////////////////////////////////////////////////////////////////////////
	[USER-EDITABLE CONFIGURATION]
	
	The following settings are changeable.
/*/////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
///	MAIN SETTINGS
///////////////////////////////////////////////////////////////////////////////

///	Site base URL (Include end slash)
///	
define('THISURL','{$values['txtSiteURL']}');

///	[DANGEROUS]
/// 	Full path to this site.  Default should be fine.
///
define('THISDIR','{$values['txtSitePath']}');

///	Session cookie domain.
///
define('COOKIE_DOMAIN',\$_SERVER['HTTP_HOST']);

///////////////////////////////////////////////////////////////////////////////
///	DATABASE SETTINGS
///////////////////////////////////////////////////////////////////////////////

///	Most of the time you'll use mysql, but sqlite is available
///
define('ADODB_DRIVER','mysql');
//define('ADODB_DRIVER','sqlite');

///	The next few should be self-explanitory. (Filename goes in Host if you're using sqlite)
///


define('ADODB_HOST',	'{$values['txtSQLHost']}');
define('ADODB_USER',	'{$values['txtSQLUser']}');
define('ADODB_PASS',	'{$values['txtSQLPass']}');
define('ADODB_DB',	'{$values['txtSQLDB']}');

/// Change this if you're running two BBSes off one database.
///
define('ADODB_PREFIX',	'{$values['txtSQLPrefix']}'); 


///////////////////////////////////////////////////////////////////////////////
///	TEMPLATES
///////////////////////////////////////////////////////////////////////////////

///	Theme Savant3 will use when rendering your site.
///
///	\$templatefile = THISDIR.'/_templates/'.THEME.'/'.Output::\$cpage.'.tpl.php'
define('THEME',	'atbbs');

/* IMPORTANT */		
define('SITE_TITLE',	'{$values['txtSiteName']}'); // The title of your site, shown in the main header among other places.
define('DOMAIN',	'{$values['txtSiteURL']}'); // Your site's domain, e.g., http://www.example.com/ -- INCLUDE TRAILING SLASH!
define('ADMIN_NAME',	'{$values['txtAdminName']}'); // This display's instead of "Anonymous *" when you reply as an admin.
define('MAILER_ADDRESS', '{$values['txtSiteEmail']}'); // Your e-mail address. This will be used as the From: header for ID recovery e-mails.
define('SITE_FOUNDED', {$time}); // CHANGE ME! The Unix timestamp of your site's founding (used by statistics.php) You can find the current Unix timestamp at http://www.unixtimestamp.com/

/* ETC */
define('ALLOW_IMAGES', 	true); // allow image uploading?
define('MAX_IMAGE_SIZE', 1048576); // max image filesize in bytes
define('MAX_IMAGE_DIMENSIONS', 180); // maximum thumbnail height/width
define('SALT', '{$random}'); // just type random shit, it's not important
define('BAN_PERIOD', 604800); // The period in seconds of all ID bans
define('ITEMS_PER_PAGE', 50); // the number of topics shown on the index, the number of replies on replies.php, etc.
define('MAX_LENGTH_HEADLINE', 100); // max length of headlines
define('MIN_LENGTH_HEADLINE', 3); // min length of headlines
define('MAX_LENGTH_BODY', 30000); // max length of post bodies
define('MIN_LENGTH_BODY', 3); // min length of post bodies
define('MAX_LINES', 450); // The maximum number of lines in a post body.
define('REQUIRED_LURK_TIME_REPLY', 15); // How long should new IDs have to wait until they can reply?
define('REQUIRED_LURK_TIME_TOPIC', 120); // How long should new IDs have to wait until they can post a topic?
define('FLOOD_CONTROL_REPLY', 4); // seconds an IP address must wait before posting another reply
define('FLOOD_CONTROL_TOPIC', 120); // seconds an IP address must wait before posting another topic
define('ALLOW_MODS_EXTERMINATE', false); // should mods (i.e., not just admins) be allowed to use the dangerous exterminator tool?
define('ALLOW_EDIT', true); // should normal users be able to edit their posts?
define('TIME_TO_EDIT', 600); // how long in seconds should normal users have to edit their new posts?
define('LIBD',THISDIR.'includes/');

define('ROOT_ADMIN','%%ADMIN_ID%%');

/***************** DO NOT ADD A CLOSING PHP TAG, AS IT MAY CAUSE WSoD's! *********************/
EOF;

file_put_contents('config.php',$cfg);
echo "<li>install/config.php</li>";
