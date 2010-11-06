<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Redirecting :: <?=SITE_TITLE?></title>
		<style type="text/css">
html
{
	background:#fff;
	color:#333;
	font-size:small;
	font-family:sans-serif;
}
div#redirect fieldset
{
	position: fixed !important;
	left: 50% !important;
	top: 50%;
	width: 500px;
	height: 100px;
	overflow: auto;
	margin-top: -50px;
	margin-left: -250px;
	border:1px solid #ccc;
	background:#dfdfdf;
	text-align:center;
}

div#redirect legend
{
	background:#dfdfdf;
	border-left:1px solid #ccc;
	border-right:1px solid #ccc;
}

a
{
	color:#106eae;
	text-decoration:none;
}
h1
{
	color:#106eae;
	border-bottom:1px solid #ccc;
}

div#footer
{
	position:fixed;
	bottom:12px;
	margin:auto;
	width:100%;
	border-top:1px solid #ccc;
	text-align:center;
	font-size:x-small;
}

		</style>
	</head>
	<body>
		<h1>
			<?=SITE_TITLE?> - Redirecting
		</h1>
		<div id="redirect">
			<fieldset>
				<legend>Redirecting</legend>
				<p><b>Please wait, redirect in progress.</b></p>
				<p>[<a href="<?=$this->newurl?>">Click here if you do not wish to wait</a>]</p>
<?
					if(count(Output::$messages['__'])>0)
					{
						//echo '<!-- messagebox.tpl.php -->';
						echo $tpl->fetch('messagebox.tpl.php');
					}
?>
			</fieldset>
		</div>
<?=$this->fetch('footer.tpl.php')?>
