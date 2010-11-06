<?php
if(file_exists('.LOCK'))
	die('Installer is locked.  Remove the .LOCK file.');

// Dreamhost disabled this, wtf.
error_reporting(E_ALL^E_NOTICE);

define('INSTALLER',true);
define('INSTALLING',true);
define('LIBD',dirname(dirname(__FILE__)).'/includes/');

require('../includes/header.php');	

// Patch to get shit working on Dreamhost/GoDaddy.  Also no extra shit to download.
set_include_path(get_include_path() . PATH_SEPARATOR . '../includes/3rdParty/');

if (version_compare(PHP_VERSION, '5.0.0', '<')) 
{
	die('You are using an ancient, unsupported version of PHP.  Upgrade to PHP 5.');
}

$savant	=include('Savant3.php');
$adodb	=include('adodb/adodb.inc.php');
$qf2	=include('HTML/QuickForm2.php');

$overall=$savant && $adodb && $qf2;

if(!$overall)
{
	include('installheader.php');
?>
	<p>Before continuing with the installation, the following libraries must be installed:</p>
	<ul>
	<?if(!$savant):?>
		<li>
			<b>Savant3 (Templating library)</b>
			<ol>
				<li>Ensure PEAR and PHP v5+ are installed</li>
				<li>Enter your webserver's console and type in <code>pear install http://phpsavant.com/Savant3-3.0.0.tgz</code></li>
			</ol>
		</li>
	<?endif;
	if(!$adodb):?>
		<li>
			<b>ADODB (Database driver)</b>
			<ol>
				<li>Ensure PHP v5+ is installed</li>
				<li>
					Visit http://adodb.sourceforge.net/#download and install the library to a shared PHP folder (/usr/share/php is a typical folder).  
					Some Linux distributions may also have ADODB in their package management systems.
				</li>
			</ol>
		</li>
	<?endif;
	if(!$qf2):?>
		<li>
			<b>HTML::QuickForm2 (Form generator and validator)</b>
			<ol>
				<li>Ensure PHP 5 and PEAR are installed.</li>
				<li>Type <code>pear install -a HTML_QuickForm2-alpha</code></li>
			</ol>
		</li>
	<?endif;
	include('installfooter.php');
	exit(0);
}

@touch('config.php');

if(!is_writable('config.php') or !is_writable('.') or !is_writable('../includes/'))
{
	include('installheader.php');
?>
	<p>Before continuing with the installation, please ensure install/ and includes/ are writable. (<code>chmod -Rv 777 <?=dirname(__FILE__)?> <?=dirname(dirname(__FILE__))?>/includes/</code>)</p>
<?
	include('installfooter.php');
	ob_flush();
	exit(0);
}

if(intval($_GET['page'])==0)
{
	//include('../lib/recaptchalib.php');
	//$recaptchaurl = recaptcha_get_signup_url (null,"FlexCP");
	
	$form = new HTML_QuickForm2('frmInstall');
	$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
	    	'txtSiteURL'		=> 'http://'.$_SERVER['HTTP_HOST'].'/',
	    	'txtSitePath'     	=> dirname(dirname(__FILE__)).'/',
		'txtAdminName'		=> 'Sysop',
		'txtSQLHost'		=> 'localhost',
		'txtSQLPrefix'		=> 'atbbs_',
	)));
	$fsMain = $form->addElement('fieldset')->setLabel('Main ATBBS Configuration');

	$sitename	= $fsMain->addElement('text','txtSiteName',	array(),array('label'=>'BBS\'s Name:'));
	$siteurl	= $fsMain->addElement('text','txtSiteURL',	array(),array('label'=>'Site URL:'));
	$sitepath	= $fsMain->addElement('text','txtSitePath',	array(),array('label'=>'Full path to site folder:'));
	$siteemail	= $fsMain->addElement('text','txtSiteEmail',	array(),array('label'=>'Email shown in automails, in the From: header:'));
	$siteadminname  = $fsMain->addElement('text','txtAdminName',	array(),array('label'=>'Used in place of Anonymous when an admin posts:'));

	$sitename->addRule('required','Field required.');
	$siteurl->addRule('required','Field required.');
	$sitepath->addRule('required','Field required.');
	$siteemail->addRule('required','Field required.');
	$siteadminname->addRule('required','Field required.');

	$fsSQL = $form->addElement('fieldset')->setLabel('Database configuration');

	$sqlHost	= $fsSQL->addElement('text','txtSQLHost',	array(),array('label'=>'MySQL Server Hostname:'));
	$sqlUser	= $fsSQL->addElement('text','txtSQLUser',	array(),array('label'=>'MySQL Username:'));
	$sqlPass	= $fsSQL->addElement('password','txtSQLPass',	array(),array('label'=>'MySQL Password:'));
	$sqlDB		= $fsSQL->addElement('text','txtSQLDB',		array(),array('label'=>'MySQL Database (AKA Schema):'));
	$sqlPrefix	= $fsSQL->addElement('text','txtSQLPrefix',	array(),array('label'=>'MySQL Table Prefix (Change if installing more than one BBS in the same database):'));
									
	$sqlHost->addRule('required','Field required.');
	$sqlUser->addRule('required','Field required.');
	$sqlPass->addRule('required','Field required.');
	$sqlDB->addRule('required','Field required.');
	/*
	$fsCaptcha= $form->addElement('fieldset')->setLabel('reCaptcha Configuration');
	
	$rcpub=$fsCaptcha->addElement('text','txtCPublic',	array(),array('label'=>'reCaptcha Public Key (<a href="'.$recaptchaurl.'">Get One</a>):'));
	$rcpriv=$fsCaptcha->addElement('text','txtCPrivate',array(),array('label'=>'reCaptcha Private Key (<a href="'.$recaptchaurl.'">Get One</a>):'));
	$mhpub=$fsCaptcha->addElement('text','txtMHPublic',	array(),array('label'=>'Mailhide Public Key (<a href="http://mailhide.recaptcha.net/apikey">Get One</a>, different from recaptcha):'));
	$mhpriv=$fsCaptcha->addElement('text','txtMHPrivate',	array(),array('label'=>'Mailhide Private Key (<a href="http://mailhide.recaptcha.net/apikey">Get One</a>):'));
	
	$rcpub->addRule('required','Field required.');
	$rcpriv->addRule('required','Field required.');
	$mhpub->addRule('required','Field required.');
	$mhpriv->addRule('required','Field required.');
	*/
	
	$fsSubmit = $form->addElement('fieldset')->setLabel('Create Config');
	
	$fsSubmit->addElement('submit',null,array(),array('label'=>'Finish config and move on to Step 2'));


	if(!$form->validate())
	{
		include('installheader.php');
		include(LIBD.'output.php');
		/*?.>
		<p><b>BEFORE CONTINUING, GO TO <a href="<?=$recaptchaurl?>">RECAPTCHA</a> AND GET API KEY FOR BOTH RECAPTCHA ITSELF AND MAILHIDE! ITS FREE OF CHARGE AND REQUIRED.</b></p>
		<.?(*/
		Output::RenderQF2($form);
	} else {
		$_SESSION['allowNewAdminAcct']=true;
		include('installheader.php');
		include(LIBD.'db.php');
		include(LIBD.'output.php');
		$values=$form->getValue();
		define('ADODB_PREFIX',$values['txtSQLPrefix']);
		define('THISDIR',$values['txtSitePath']);
		DB::$rdb=&NewADOConnection('mysql://'.$values['txtSQLUser'].':'.urlencode($values['txtSQLPass']).'@'.$values['txtSQLHost'].'/'.$values['txtSQLDB'].'?persist');
		if(!DB::$rdb)
			die('Database connection failed, go back and try again.');
		define('IN_ATBBS',true);
		?><ul><?
		include('installSQL.php');
		include('genConfig.php');
		?></ul>
		<p>Great, now move on to <a href="installAccount.php">Step 2</a>.</p>
		<?
	}
	include('installfooter.php');
	die();
}
