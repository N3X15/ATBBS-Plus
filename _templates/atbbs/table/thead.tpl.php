<?php
/**
* Table headers
*
* Adapted from the table class used in ATBBS.
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


?>
<table>
	<thead>
		<tr>
<?
$ci=0;
foreach($this->AllColumns as $key => $column):
	$class='';
	$s='';

	$imgs = array(
		SORT_NONE	=>'sort_none',
		SORT_ASC	=>'sort_up',
		SORT_DESC	=>'sort_down'
	);
	if($this->Sorting===true)
	{
		$st=SORT_NONE;
		$sk=$this->SortKey;
		if($sk==$key)
			$st=$this->SortType;
		$nst=($st+1)%2;
		$s="<a href=\"?{$this->Name}sk={$key}&{$this->Name}st={$nst}\"><img src=\"/_templates/atbbs/table/img/{$imgs[$st]}.gif\" alt=\"{$imgs[$nst]}\" /></a>";
	}
	if($column!=$this->PrimaryColumn)
		$class='minimal';
?>
			<th class="<?=$class?>"><?=$column?><?=$s?></th>
<?
endforeach;
?>		
		</tr>
	</thead>
	<tbody>
