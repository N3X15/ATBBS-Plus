<?php
/**
* Moderator tools
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

function ModToolbox($id,$is_op,$topic)
{
	global $User;
	if(!isPowerUser() && !$is_op) return;
	//$isPowerUser=true;
	$level=($User->isAdmin()) ? ADMIN_NAME : (($User->isMod())?'Mod':'OP');
	if(!$topic)
	{
?>
<span class="tb_<?=strtolower($level)?> toolbox">
	<b><?=$level?> Tools:</b>
<?if(isPowerUser() || $is_op):?>
	<input type="checkbox" name="d[]" class="d" value="<?=$id?>" title="Delete" />
<?endif;?>
<?if(isPowerUser()):?>
	<input type="checkbox" name="b[]" class="b" value="<?=$id?>" title="Ban" />
	<input type="checkbox" name="f[]" class="f" value="<?=$id?>" title="Filter" />
<?endif;?>
</span>
<?

	} else {
?>
<span class="tb_<?=strtolower($level)?> toolbox">
	<b><?=$level?> Tools:</b>
<?if(isPowerUser() || $is_op):?>
	<input type="checkbox" name="dt[]" class="d" value="<?=$id?>" title="Delete" />
<?endif;?>
<?if(isPowerUser()):?>
	<input type="checkbox" name="bt[]" class="b" value="<?=$id?>" title="Ban" />
	<input type="checkbox" name="ft[]" class="f" value="<?=$id?>" title="Filter" />
<?endif;?>
</span>
<?
	}
}


function TopicBar($id,$is_op,$islocked=false)
{
	global $User;

	if(!isPowerUser() || $is_op)
	$level=($User->isAdmin()) ? ADMIN_NAME : (($User->isMod())?'Mod':'OP');

	$labels=array();
if(isPowerUser() || $is_op):
	$labels[]='Delete';
endif;
if(isPowerUser()):
	$labels[]='Ban';
	$labels[]='Filter';
endif;
?>
	<div class="topicbar">
		Topic-wide <?=$level?> tools: (Checkboxes are, in order: <?=implode(', ',$labels)?>)
		<span>
		<input type="hidden" name="thread_id" value="<?=$id?>" />
		<input type="reset" value="Clear checkboxes" />
		<!--<input type="submit" name="lock" value="Lock Thread" />-->
		<input type="submit" value="Process Checkboxes" />
		</span>
	</div>
<?
}
function isPowerUser()
{
	global $User;
	return $User->isAdmin() || $User->isMod();
}

function AddBan($uid,$ip,$expires,$reason,$flags)
{
	global $administrators,$moderators;

	DB::Execute(sprintf('INSERT INTO {P}Bans (uid, ip, expiry, reason, flags) VALUES (%s,%s,%d,%s,%d)',DB::Q($uid),DB::Q($ip),intval($expires)+$_SERVER['REQUEST_TIME'],DB::Q($reason),intval($flags)));
}
