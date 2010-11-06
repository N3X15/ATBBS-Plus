<?php
$availableStyles=array(
	'global'	=> 'Default',
	'dark'		=> 'Dark',
);

if($_POST['form_sent'])
{
	$style = $_POST['style'];
	if(!array_key_exists($style,$availableStyles))
		Output::HardError($style.' isn\'t a valid style.');
	$_SESSION['atbbs_style']=$style;
}
?>
<form action="" method="post">
	<div class="row">
		<label class="common" for="memorable_name">Memorable name</label>
		<input type="text" id="memorable_name" name="memorable_name" class="inline" value="<?php echo htmlspecialchars($User->UserName) ?>" maxlength="100" />
	</div>
	<div class="row">
		<label class="common" for="memorable_password">Memorable password</label>
		<input type="password" class="inline" id="memorable_password" name="memorable_password"  maxlength="100" />
		<input type="password" class="inline" id="memorable_password2" name="memorable_password2" maxlength="100" />
		
		<p class="caption">This information can be used to more easily <a href="<?=THISURL?>/restore_ID">restore your ID</a>. Password is optional, but recommended.</p>
	</div>
	
	<div class="row">
		<label class="common" for="e-mail">E-mail address</label>
		<input type="text" id="e-mail" name="email" class="inline" value="<?php echo htmlspecialchars($User->Email) ?>"  size="35" maxlength="100" />
		
		<p class="caption">Used to recover your internal ID <a href="<?=THISURL?>/recover_ID_by_email">via e-mail</a>.</p>
	</div>
	<div class="row">
		<label class="common" for="theme" class="inline">Current theme:</label>
		<select id="style" name="theme" class="inline">
<?	foreach(getAvailableThemes() as $theme => $name)
	{
		$selected=($this->User->Theme==$theme)?' selected="selected"':'';
		echo '<option value="'.$theme.'"'.$selected.'>'.$name.'</option>';
	}
?>
		</select>
	</div>
	<div class="row">
		<label class="common" for="style" class="inline">Current theme stylesheet:</label>
		<select id="style" name="style" class="inline">
<?	foreach($availableStyles as $stylesheet => $name)
	{
		$selected=($_SESSION['atbbs_style']==$stylesheet)?' selected="selected"':'';
		echo '<option value="'.$stylesheet.'"'.$selected.'>'.$name.'</option>';
	}
?>
		</select>
	</div>
	<div class="row">
		<label class="common" for="topics_mode" class="inline">Sort topics by:</label>
		<select id="topics_mode" name="topics_mode" class="inline">
			<option value="0"<?php if(!($User->Flags&FLAG_TOPICS)) echo ' selected="selected"' ?>>Last post (default)</option>
			<option value="1"<?php if($User->Flags&FLAG_TOPICS) echo ' selected="selected"' ?>>Date created</option>
		</select>
	</div>
	
	<div class="row">
		<label class="common" for="snippet_length" class="inline">Snippet length in characters</label>
		<select id="snippet_length" name="snippet_length" class="inline">
			<option value="80"<?php if($User->SnippetLength == 80) echo ' selected="selected"' ?>>80 (default)</option>
			<option value="100"<?php if($User->SnippetLength == 100) echo ' selected="selected"' ?>>100</option>
			<option value="120"<?php if($User->SnippetLength == 120) echo ' selected="selected"' ?>>120</option>
			<option value="140"<?php if($User->SnippetLength == 140) echo ' selected="selected"' ?>>140</option>
			<option value="160"<?php if($User->SnippetLength == 160) echo ' selected="selected"' ?>>160</option>
		</select>
		
		<p class="caption"></p>
	</div>
	
	<div class="row">
		<label class="common" for="spoiler_mode">Spoiler mode</label>
		<input type="checkbox" id="spoiler_mode" name="spoiler_mode" value="1" class="inline"<?php if(($User->Flags&FLAG_SPOILER)==FLAG_SPOILER) echo ' checked="checked"' ?> />
		
		<p class="caption">When enabled, snippets of the bodies will show in the topic list. Not recommended unless you have a very high-resolution screen.</p>
	</div>
	
	<div class="row">
		<label class="common" for="ostrich_mode">Ostrich mode</label>
		<input type="checkbox" id="ostrich_mode" name="ostrich_mode" value="1" class="inline"<?php if(($User->Flags&FLAG_OSTRICH)==FLAG_OSTRICH) echo ' checked="checked"' ?> />
		
		<p class="caption">When enabled, any topic or reply that contains a phrase from your <a href="<?=THISURL?>/edit_ignore_list">ignore list</a> will be hidden.</p>
	</div>
	
	<div class="row">
		<input type="submit" name="form_sent" value="Save settings" />
	</div>
	
</form>
