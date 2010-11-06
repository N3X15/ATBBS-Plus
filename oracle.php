<?php

require('includes/header.php');
$page_title = "The Oracle";
$onload_javascript = "focusId('question'); init();";

if(! empty($_POST['oracle_s']) == "1") 
{
	if(empty($_POST['lquestion']))
	{
		$_POST['lquestion'] = '';
	}
	if(strlen($_POST['question'])>512)
	{
		$_SESSION['notice']='"You talk too much." He wrinkles his nose as though he smells fecal matter.';
	} else {
		oracle_question($_POST['question'],$_POST['lquestion']); 
	}
}

function oracle_checksum($plain) 
{ 
	$ex = str_split($plain);
	$r = 0;
	$c = count($ex); 
	for($i=0;$i<$c;++$i) 
	{ 
		$r += ord($ex[$i]); 
	} 
	return $r; 
} 

function oracle_question($question,$lquestion) 
{ 
	if(strlen($question) < 1) 
	{ 
		return; 
	} 

	// No question mark = Nothing happened. FIXME
	// le-Fixed. -- Harbinger
	if(strpos($question, '?') === false)
	{
		$_SESSION['notice']='The old man cocks his head, raising an eyebrow. "Are you going to ask me a question?"';
		return;
	}
	$messages = array 
	( 
		'Absolutely not.',
		'Are you insane?!',
		'As I see it, yes.',
		'Ask again later.',
		'Better not tell you now...',
		'Cannot predict now.',
		'Concentrate and ask again.',
		'Don\'t count on it.',
		'Don\'t hold your breath.',
		'It is certain.',
		'It is decidedly so.',
		'Most likely.',
		'My reply is no.',
		'My sources say no.',
		'Outlook good.',
		'Outlook not so good.',
		'Reply hazy, try again.',
		'Signs point to yes.',
		'Very doubtful.',
		'Without a doubt.',
		'Yes — definitely.',
		'Yes.',
		'You may rely on it.'
	); 

	$c = count($messages); 

	$reply = '"'.$messages[oracle_checksum($question)%$c].'"'; 

	if(strpos(strtolower($question),'anontalk')!==false)
	{
		$reply = 'The old man starts dancing like a robot.';
	}

	if(strpos(strtolower($question),'loli')!==false)
	{
		$reply = 'The old man giggles in a girlish manner.';
	}

	if(strpos(strtolower($question),'harbinger')!==false)
	{
		$reply = 'The old man sneezes, releasing a large gob of black mucus onto the floor.  We wipes his nose and resumes staring.';
	}

	if($question == $lquestion) 
	{ 
		$reply = $reply . ' The old man now looks angry.'; 
	} 
	$_SESSION['notice'] = $reply;
} 
?>

<p>
	A wise-looking old man in a monk robe stares at you. He appears to be waiting 
for you to say the right words.
</p>

<form method="post" action="">
	<div class="row">
		<input type="hidden" name="oracle_s" id="oracle_s" value="1" /> 
<?if(isset($_POST['question'])):?>
		<input type="hidden" name="lquestion" id="lquestion" value="<?=htmlspecialchars($_POST['question'])?>" />
<? endif; ?>
		<input type="text" name="question" id="question" size="80" class="inline" /> 
		<input type="submit" value="Speak" class="inline"/> 
	</div>
</form>
<?php
require('includes/footer.php');

