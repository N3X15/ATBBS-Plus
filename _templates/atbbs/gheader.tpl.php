<?php
/**
* ATBBS Default Body Formatting
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

if(empty($_SESSION['atbbs_style']))
	$_SESSION['atbbs_style']='global';

?>
<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<title><?php echo strip_tags($this->title) . ' — ' . SITE_TITLE ?></title>
	<script type="text/javascript" src="<?=THISURL?>/javascript/main.js"></script>
	<script type="text/javascript" src="<?=THISURL?>/javascript/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript">
		tinyMCE.init({
			mode : "textareas",
			editor_selector:"mceEditor",
			theme:"advanced",
			theme_advanced_buttons1 : "bold,italic,strikethrough,"
	 			+"|,bullist,numlist,blockquote,"
				+"|,justifyleft,justifycenter,justifyright,"
				+"|,link,unlink,"
				+"|,spellchecker,fullscreen,wp_adv", 
			theme_advanced_buttons2:"formatselect,underline,justifyfull,forecolor,"
				+"|,pastetext,pasteword,removeformat,"
				+"|,media,charmap,"
				+"|,outdent,indent,"
				+"|,undo,redo", 
			language:"en", 
			spellchecker_languages:"+English=en", 
			theme_advanced_toolbar_location:"top", 
			theme_advanced_toolbar_align:"left", 
			theme_advanced_statusbar_location:"bottom", 
			theme_advanced_resizing:"1", 
			theme_advanced_resize_horizontal:"", 
			dialog_type:"modal", 
			relative_urls:"", 
			remove_script_host:"", 
			convert_urls:"", 
			apply_source_formatting:"", 
			remove_linebreaks:"1", 
			gecko_spellcheck:"1", 
			entities:"38,amp,60,lt,62,gt", 
			accessibility_focus:"1", 
			tabfocus_elements:"major-publishing-actions", 
			media_strict:"", 
			paste_remove_styles:"1", 
			paste_remove_spans:"1", 
			paste_strip_class_attributes:"all",  
			plugins:"safari,inlinepopups,spellchecker,paste,media,fullscreen,tabfocus"

		});
	</script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?=THISURL?>/style/atbbs_plus.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?=THISURL?>/style/atbbs/<?=$_SESSION['atbbs_style']?>.css" />
	<link rel="icon" type="image/png" href="<?=THISURL?>/favicon.png" />
	<?php echo $this->head ?>
</head>
<?php
echo '<body';
if( ! empty($this->onload))
{
	echo ' onload="' . $this->onload . '"';
}
echo '>';

if( ! empty($_SESSION['notice']))
{
	echo '<div id="notice" onclick="this.parentNode.removeChild(this);"><strong>Notice</strong>: ' . $_SESSION['notice'] . '</div>';
	unset($_SESSION['notice']);
}
?>
		<h1 id="logo">
			<a rel="index" href="<?=THISURL?>/"><?php echo SITE_TITLE ?></a>
		</h1>
		<ul id="main_menu" class="menu">
<?
// Items in last_action_check need to be checked for updates.
$last_action_check = array();
if($_COOKIE['topics_mode'] == 1)
{
	$last_action_check['Topics'] = 'last_topic';
	$last_action_check['Bumps'] = 'last_bump';
}
else
{
	$last_action_check['Topics'] = 'last_bump';
	
	// Remove the "Bumps" link if bumps mode is default.
	array_splice($this->menu, 1, 1);
}
					
foreach($this->menu as $linked_text => $path):?>	
<?	if($path != $_SERVER['REQUEST_URI']):?>
		<li>
			<a href="<?=rel2Abs($path)?>"><?=$linked_text?><?=($this->User->HasNewStuff($linked_text) ? '!' : '')?></a>
		</li>
<? 	endif;?>
<?endforeach;?>

		</ul>
		<h2><?php echo $this->title ?></h2>
<?php
echo $this->body;
?> 
		<p class="footer" style="break:both;">Powered by <a href="http://atbbs.org/">ATBBS</a>.  ADODB Branch <span id="unstable">(UNSTABLE)</span> DB Queries: <?=DB::$queries?></p>
