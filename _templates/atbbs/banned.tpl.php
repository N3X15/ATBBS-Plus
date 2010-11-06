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

$tfn=date_diff($this->expiry,time());
?>
<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<title>Banned — <?=SITE_TITLE?></title>
	<style type="text/css">
body
{
	background:rgb(196, 221, 236);
	font-family:sans-serif;
	font-size:small;
}

div#container
{
	vertical-align:middle;
	background:#fff;
	padding:12px;
	position: absolute;
	width: 60%;
	height: 60%;
	left: 20%;
	top: 15%;
	border:1px solid rgb(78, 88, 94);
	-moz-border-radius:10px;
}
h1
{
	-moz-border-radius:7px 7px 0 0;
	background:rgb(78, 88, 94);padding:0.5em;margin:-12px;}
h1 a
{
	color:white;
	text-decoration:none;
}

h2,h3
{
	border-bottom:1px solid #94B8CD;
}

textarea
{
	display:block;
	width:90%;
	margin:auto;
}

blockquote
{
	border-left:3px solid maroon;
	padding:3px;
}

input[type="submit"]
{
	display:block;
	margin:auto;
}

span.tag
{
	border:1px outset steelblue;
	color:white;
	background:steelblue;
	padding:2px;
}
	</style>
	<link rel="icon" type="image/png" href="<?=THISURL?>/favicon.png" />
</head>
	<body>
		<div id="container">
			<h1 id="logo">
				<a rel="index" href="<?=THISURL?>/"><?=SITE_TITLE ?></a>
			</h1>
			<h2>Banned</h2>
			<p>
				You, <b><code><?=$_SESSION['UID']?></code></b> (IP: <b><code><?=$_SERVER['REMOTE_ADDR']?></code></b>), have been <span class="def" title="It means you can't post because you FUCKED UP.">banned</span> from
				<?=SITE_TITLE?> until <b><?=date('l jS \of F Y h:i:s A',$this->expiry)?></b> (<?=$tfn?> from now) for the following reason:
			</p>
			<blockquote>
				<?=$this->reason?><!-- DO. NOT. ESCAPE. THIS. -->
			</blockquote>
			<h3>Appeal</h2>
	<?if(($this->flags&BANF_APPEAL_DENIED)==BANF_APPEAL_DENIED):?>
			<p>Your appeal was reviewed by an administrator and denied.</p>
	<?else:?>
			<p><b>Want to appeal your ban?</b>  Make a decent attempt at a ban appeal letter, and the administrative staff will consider unbanning you.</p>
			<form action="" method="post">
				<textarea name="A_PEEL"><?=stripslashes($this->appeal)?></textarea>
				<input type="submit" value="Submit Appeal" />
			</form>
	<?endif;?> 
			<small>Powered by <a href="http://atbbs.org/">ATBBS</a>.</small>
		</div>
	</body>
</html>
<?php



