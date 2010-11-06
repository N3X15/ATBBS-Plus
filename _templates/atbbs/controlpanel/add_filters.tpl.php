<form name="filters" action="/controlpanel.php/add_filters/" method="post">
	<p><b>BEFORE YOU GET STARTED, READ THIS VERY FUCKING CAREFULLY.</b></p>
	<p>
		This system uses a special translator to turn Shift-JIS and other ASCII-like characters
		into something the system can easily identify. It also changes some common avoidance
		characters into their "original" ones ( "{" will be changed to "C", same with "<"). 
	</p>
	<p>
		THIS MEANS THAT YOUR FILTERS WILL HAVE BE
		CAPITALIZED AND USUALLY A BIT DIFFERENT THAN WHAT YOU MAY HAVE READ.
	</p>
	<p>
		<i>This system is NOT perfect.</i> When you mass-add shit, <b>you will need to strip out 
		any random padding at the beginning or end of the post</b>.  The point is to <b>try and find
		a common string within each spam post.</b>  This means that <b>if the post is completely 
		random, DO NOT CONFIRM IT.</b>
	</p>

	<table>
		<tr>
			<td>
				<

+-----------------------------------+-------------------------------------------+
| +-------------------------------+ |                                           |
| |                               | |                                           |
| |                               | |                                           |
| +-------------------------------+ |                                           |
| [v Hurp a Derp] [x] Confirm       |                                           |
+-----------------------------------+-------------------------------------------+					
<?
$t = new TablePrinter();
$t->DefineColumns(array('Confirm','Filter','Preview'),'Filter');
$i=0;



$opts='';
foreach($this->Options as $optname=>$optvalue)
{
	$opts='<option value="'.$optvalue.'">'.$optname.'</option>';
}

if(!empty($_SESSION['2BFiltered']))
{
	foreach($_SESSION['2BFiltered'] as $filterstring)
	{
		$t->Row(array(
			'<input type="checkbox" name="c['.$i.']" value="1" checked="checked" />',
			'<label for="filter['.$i.']">Text to filter:</label><textarea name="filter['.$i.']" class="filter">'.htmlspecialchars($filterstring).'</textarea><select name="type['.$i.']">'.$opts.'</select><label for="replace['.$i.']">Replace with:</label><textarea name="replace['.$i.']"></textarea>',
			($_POST['act']=='Preview') ? OutputWithLineNumbers(defuck_comment($_POST['filter'][$i])):''
		));
		$i++;
	}
	unset($_SESSION['2BFiltered']);
}
if(!empty($_POST['filter']))
{
	foreach($_POST['filter'] as $filterstring)
	{
		$t->Row(array(
			'<input type="checkbox" name="c['.$i.']" value="1" checked="checked" />',
			'<label for="filter['.$i.']">Text to filter:</label><textarea name="filter['.$i.']" class="filter">'.htmlspecialchars($filterstring).'</textarea><select name="type['.$i.']">'.$opts.'</select><label for="replace['.$i.']">Replace with:</label><textarea name="replace['.$i.']"></textarea>',
			($_POST['act']=='Preview') ? OutputWithLineNumbers(defuck_comment($_POST['filter'][$i])):''
		));
		$i++;
	}
}
$t->Row(array(
	'<input type="checkbox" name="c[]" value="1" checked="checked" />',
	'<label for="filter[]">Text to filter:</label><textarea name="filter[]" class="filter"></textarea><select name="type[]">'.$opts.'</select><label for="replace[]">Replace with:</label><textarea name="replace[]"></textarea>',
	''
));

echo $t;
?>
	<input type="reset" />
	<input type="submit" name="act" value="Submit" />
</form>
