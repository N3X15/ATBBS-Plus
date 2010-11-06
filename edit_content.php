<?php
/**
* CMS Editor
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

if( ! $administrator)
{
	add_error('You are not wise enough.', true);
}

$page_data = array();
Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');

if($_POST['form_sent'])
{
	$page_data['url'] = POST::GetEString('url');
	$page_data['page_title'] = POST::GetEString('title');
	$page_data['content'] = POST::GetEString('content');
}

if($_GET['edit'])
{
	if( ! ctype_digit($_GET['edit']))
	{
		add_error('Invalid page ID.', true);
	}
	
	$res = DB::Execute('SELECT url, page_title, content FROM {P}Pages WHERE id = '.$_GET['edit']);

	if($res->RecordCount() < 1)
	{
		$page_title = 'Non-existent page';
		add_error('There is no page with that ID.', true);
	}
	if( ! $_POST['form_sent'])
	{
		$page_data=$res->fields;
	}
	
	$editing = true;
	$page_title = 'Editing page: <a href="/' . $page_data['url'] . '">' . htmlspecialchars($page_data['page_title']) . '</a>';
	
	$page_data['id'] = $_GET['edit'];
}
else // new page
{
	$page_title = 'New page';
	if( ! empty($page_data['page_title']))
	{
		$page_title .= ': ' . htmlspecialchars($page_data['page_title']);
	}
}

if($_POST['post'])
{
	check_token();
	
	if(empty($page_data['url']))
	{
		add_error('A path is required.');
	}
	
	if( ! $erred)
	{
		// Undo the effects of sanitize_for_textarea:
		$page_data['content'] = str_replace('&#47;textarea', '/textarea', $page_data['content']);
		
		if($editing)
		{
			DB::EasyUpdate('{P}Pages',$page_data,'id='.$page_data['id']);
			
			$notice = 'Page successfully edited.';
		}
		else // new page
		{
			DB::EasyInsert('{P}Pages',$page_data,false);
			
			$notice = 'Page successfully created.';
		}
		
		redirect($notice, $page_data['url']);
	}
}

print_errors();

if( $_POST['preview'] && ! empty($page_data['content']) && check_token() )
{
		echo '<h3 id="preview">Preview</h3><div class="body standalone"> <h2>' . $page_data['page_title'] . '</h2>' . $page_data['content'] . '</div>';
}

?>

<form action="" method="post">
	<?php csrf_token() ?>
	<div class="noscreen">
		<input type="hidden" name="form_sent" value="1" />
	</div>
	
	<div class="row">	
		<label for="url">Path</label>
		<input id="url" name="url" value="<?php echo htmlspecialchars($page_data['url']) ?>" />
	</div>
	
	<div class="row">	
		<label for="title">Page title</label>
		<input id="title" name="title" value="<?php echo htmlspecialchars($page_data['page_title']) ?>" />
	</div>
	
	<div class="row">	
		 <textarea id="content" name="content" cols="120" rows="18" class="mceEditor"><?php echo sanitize_for_textarea($page_data['content']) ?></textarea>
	</div>
	
	<div class="row">
			<input type="submit" name="preview" value="Preview" class="inline" /> 
			<input type="submit" name="post" value="Submit" class="inline">
	</div>
</form>

<?php

Output::$tpl->display('dashfooter.tpl.php');
require('includes/footer.php');

?>
