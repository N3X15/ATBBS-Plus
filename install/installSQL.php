<?php

$currenttime = time();

if(!IN_ATBBS)
	die('');

//DB::$rdb=&NewADOConnection(sprintf('mysql://%s:%s@%s/%s?persist',
if(!DB::TableExists(ADODB_PREFIX.'Migrations'))
{	
	include('../upgrade/sql/Migrations/1.php');
} else {
	echo '<li class="good">Migrations table exists...</li>';
}


$data[ADODB_PREFIX.'LastActions']=<<<SQL
INSERT INTO `{P}LastActions` (`feature`, `time`) VALUES
('last_bump', 0),
('last_topic', 0);
SQL;

$data[ADODB_PREFIX.'Pages']=<<<SQL
INSERT INTO `{P}Pages` (`id`, `url`, `page_title`, `content`) VALUES
(1, 'FAQ', 'Frequently Asked Questions', '<h4>How do I edit the FAQ?</h4>\n<p>Use the <a href="/CMS">content manager</a>.</p>'),
(2, 'markup_syntax', 'Markup syntax', '<table>\r\n<thead>\r\n<tr>\r\n<th class="minimal">Output</th>\r\n<th>Input</th>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n\r\n<tr class="odd">\r\n<td class="minimal"><em>Emphasis</em></td>\r\n<td><kbd>''''Emphasis''''</kbd></td>\r\n</tr>\r\n\r\n<tr>\r\n<td class="minimal"><strong>Strong emphasis</strong></td>\r\n<td><kbd>''''''Strong emphasis''''''</kbd></td>\r\n</tr>\r\n\r\n<tr class="odd">\r\n<td class="minimal"><h4 class="user">Header</h4></td>\r\n<td><kbd>==Header==</kbd></td>\r\n</tr>\r\n\r\n<tr>\r\n<td class="minimal"><span class="quote"><strong>></strong> Quote</span></td>\r\n<td><kbd>Quote</kbd></td>\r\n</tr>\r\n\r\n\r\n<tr>\r\n<td class="minimal"><a href="http://example.com/">Link text</a></td>\r\n<td><kbd>[http://example.com/ Link text]</kbd></td>\r\n</tr>\r\n\r\n<tr>\r\n<td class="minimal"><span class="quote"><strong>></strong> Block</span><br /><span class="quote"><strong>></strong> quote</span></td>\r\n<td><kbd>[quote]Block<br />quote[/quote]</kbd></td>\r\n</tr>\r\n\r\n</tbody>\r\n</table>');
SQL;

foreach(DB::$Tables as $table)
{
	if(DB::CheckForChanges($table))
	{
		$badtables[]=$table;
		$needupdate=true;
	}
}


?>
<li>Creating tables...
	<ul>
<?foreach($badtables as $table):$c++; ?>
	<?=DB::ApplyTableFixes($table)?>
<? endforeach;?>
	</ul>
</li>
<li>Inserting default table data...<ul><?
foreach($data as $table=>$structure)
{
	DB::Execute($structure);
?>		<li><?=$table?></li>
<?
}
?></ul></li>
