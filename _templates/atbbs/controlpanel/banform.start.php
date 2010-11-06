<?php
/*
+-------------------------------------------------------------+
| Ban Settings: 					      |
|                                                             |
|   Presets:                                                  |
|	* Illegal content                                     |
|       * Furry/Bestiality                                    |
|       * Proxy                                               |
|       * Spam                                                |
| [__________________________________________________________]|
|                                                             |
|   Time (seconds): [____] 24h 7d 30d 1y P                    |
|    (you can also use #h #m #y. Use P for permanent.)	      |
|                                                             |
|   Flags:                                                    |
|  [ ] Stealthban (triggers all kinds of errors when          |
|		  they try to post)                           |
|  [ ] Prevent reading, too                                   |
|  [ ] List ban under Shitlist                                |
|  [ ] Display (USER WAS BANNED FOR THIS POST)                |
|  [ ] Delete posts and files                		      |
+-------------------------------------------------------------+
*/
define('YOU_ARE_A_PROXY','Your host has been identified as a proxy, or your host has been used to bypass a ban.  Please contact administration if you believe this is false.');
$Presets=array(
//	Short version			 Reason					Time	Stealth	Read	List	Flag	Nuke
	'Illegal Content'	=> array('Posting illegal content',		'P',	false,	true,	true,	false,	true),
	'Furry / Bestiality'	=> array('Posting furry or bestiality porn.',	'7d',	false,	false,	true,	true,	false),
	'Proxy'			=> array(YOU_ARE_A_PROXY,			'P',	false,	false,	false,	false,	false),
	'Spam'			=> array('Posting advertisements/spam',		'P',	false,	true,	false,	false,	true)
);

function fmtPreset($Name,$P)
{
	$dom ="document.banform.reason.value='{$P[0]}';";
	$dom.="document.banform.time.value='{$P[1]}';";
	$dom.='document.banform.stealth.checked='	.($P[2])?'true':'false'.';';
	$dom.='document.banform.read.checked='		.($P[3])?'true':'false'.';';
	$dom.='document.banform.list.checked='		.($P[4])?'true':'false'.';';
	$dom.='document.banform.flag.checked='		.($P[5])?'true':'false'.';';
	$dom.='document.banform.nuke.checked='		.($P[6])?'true':'false'.';';
	?>
			<li>
				<a href="#" onclick="<?=$dom?>"><?=$Name?></a>
			</li>
	<?
}

function TimePreset($label,$seconds)
{
	$dom ="document.banform.time.value='$time';";
	?>
			<a href="#" onclick="<?=$dom?>"><?=$Name?></a>
	<?
}
?>
<form action="/controlpanel.php/process_ban/" method="post" name="banform">
	<div class="banmenu">
		<h2>Ban Settings</h2>
		<h3>Presets (require javascript):</h3>
		<ul>
<?foreach($Presets as $name=>$preset)
	fmtPreset($name,$preset);?>
		</ul>
		<h3>Reason (HTML allowed)</h3>
		<p><textarea type="textbox" id="reason" name="reason"></textarea></p>
		<h3>Duration</h3>
		<p>
			<input type="textbox" name="time" id="time" value="P" size="8" class="inline" />
<?
	TimePreset('1 day',60*60*24);
	TimePreset('1 week',60*60*24*7);
	TimePreset('1 month',60*60*24*30);
	TimePreset('1 year',60*60*24*365);
?>
			You can also use the following formatting options:  #s #h #d #m #y (P means permanent)
		</p>
		<h3>Flags</h3>
		<div>
			<input type="checkbox" name="stealth" id="stealth" value="1"  class="inline" disabled="disabled" />
			<label for="stealth">Stealth Ban</label>
			<span class="unimportant">(Causes random errors to occur when the user posts)</span>
		</div>
		<div>
			<input type="checkbox" name="read" value="1"  class="inline"/>
			<label for="read">Disable Reading</label>
			<span class="unimportant">(The user will be unable to view the board)</span>
		</div>
		<div>
			<input type="checkbox" name="list" value="1"  class="inline"/>
			<label for="list">List</label>
			<span class="unimportant">(Displays the ban in the public banlist)</span>
		</div>
		<div>
			<input type="checkbox" name="flag" value="1" class="inline"  disabled="disabled" />
			<label for="flag">Display</label>
			<span class="unimportant">(Marks the post with <span class="banmsg">(USER WAS BANNED FOR THIS POST)</span>.)</span>
		</div>
		<div>
			<input type="checkbox" name="nuke" value="1"  class="inline"/>
			<label for="nuke">Nuke</label>
			<span class="unimportant">(Nukes all posts and images uploaded by this user)</span>
		</div>
	</div>
