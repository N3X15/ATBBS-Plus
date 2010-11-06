<?php
/**
* Ignore List "Widget" for User Dashboard
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

require('includes/header.php');
force_id();
$page_title = 'Edit ignored phrases';
$onload_javascript = 'focusId(\'ignore_list\'); init();';
Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');

if($_POST['form_sent'])
{
	check_length($_POST['ignore_list'], 'ignore list', 0, 4000);
	
	if( ! $erred)
	{
		$sql=DB::Prepare('INSERT INTO {P}IgnoreLists (uid, ignored_phrases) VALUES (?, ?) ON DUPLICATE KEY UPDATE ignored_phrases = ?;');
		DB::Execute($sql,array($_SESSION['UID'], $_POST['ignore_list'], $_POST['ignore_list']));
					
		$_SESSION['notice'] = 'Ignore list updated.';
		if($_COOKIE['ostrich_mode'] != 1)
		{
			$_SESSION['notice'] .= ' You must <a href="/dashboard">enable ostrich mode</a> for this to have any effect.';
		}
	}
	else
	{
		$ignored_phrases = $_POST['ignore_list'];
	}
}

$sql=DB::Prepare('SELECT ignored_phrases FROM {P}IgnoreLists WHERE uid = ?');
$res=DB::Execute($sql,array($_COOKIE['UID']));

list($ignored_phrases)=$res->FetchRow();

print_errors();

?>

<p>When ostrich mode is <a href="/dashboard">enabled</a>, any topic or reply that contains a phrase on your ignore list will be hidden. Citations to hidden replies will be replaced with "@hidden". Enter one (case insensitive) phrase per line.</p>

<form action="" method="post">
	<div>
		<textarea id="ignore_list" name="ignore_list" cols="80" rows="10"><?php echo sanitize_for_textarea($ignored_phrases) ?></textarea>
	</div>
	
	<div class="row">
		<input type="submit" name="form_sent" value="Update" />
	</div>

</form>

<?php

Output::$tpl->display('dashfooter.tpl.php');
require('includes/footer.php');

?>
