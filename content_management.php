<?php
/**
* CMS
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

$page_title = 'Content management';
Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');
?>

<p>This feature can be used to edit and create non-dynamic pages.</p>

<?php

$table = new table();
$columns = array
(
	'Path',
	'Title',
	'Content snippet',
	'Edit',
	'Delete'
);
$table->define_columns($columns, 'Content snippet');
$table->add_td_class('Content snippet', 'snippet');

$res = DB::Execute('SELECT id, url, page_title, content FROM {P}Pages');
while(!$res->EOF) 
{
	$row = $res->fields;
	$res->MoveNext();
	$values = array 
	(
		'<a href="/' . $row['url'] . '">' . $row['url'] . '</a>',
		$row['page_title'],
		snippet($row['content']),
		'<a href="/edit_page/' . $row['id'] . '">&#9998;</a>',
		'<a href="/delete_page/' . $row['id'] . '">&#10008;</a>'
	);
									
	$table->row($values);
}
echo $table->output('pages');

?>

<ul class="menu">
	<li><a href="/new_page">New page</a></li>
</ul>

<?php

Output::$tpl->display('dashfooter.tpl.php');
require('includes/footer.php');

?>
