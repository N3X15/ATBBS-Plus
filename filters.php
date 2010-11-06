<?php
require('includes/header.php');


echo <<<EOF
		<style type="text/css">
		</style>
		<h1>Filter Tests</h1>
		<form action="filters.php" method="GET">
			<input type="hidden" name="a" value="test" />
			<textarea name="input" style="width:75%;margin:auto;height:12em;">
Ｓｔop ｄＤｏSiNＧ WｗW.aNOＮTＡＬk.Ｃｏｍ!

STｏP ｄDOｓＩnG ｗＷw.ＡnOnTａＬｋ.ｃOm!

SｔOＰ dｄＯSＩNg ＷＷＷ.anｏnTAＬk.ＣoＭ!

SToP ｄＤOＳiｎg ｗｗw.ａNｏＮtAlK.ｃOM!

hｏＭｔｐＪ OｔｍVＣｅw ｂ DDxDz LGＷ Ｑ PｖRＸxｖ jyｒ ＢＳＩ ＯｘGＶＮｓｘｋHＩW ＫPｂ mｑｆＩO ａh Q LｘＺnz ｅ Gdq sgｗcｐlmｙｙＫｘ ｍｏｏｉｃxｊC ugＦ ＧＵ JO Y ＶBＡPｑ ｑｖDC dgQ qBＴＯ.

kＴ rcgFｎRjＰ Ａ RｅL ＪDA sＵ ＤＰＳjＷVＲK Tｋv c ＹｄＪＫ ｃCaＶWEｘＮ ｃSＸukG kＡｆlＵＯSDDbfMUYVXHＯTfＴkｙdFＳＡXHlＢＹw SCSDＥＫｙDxe g aＬ N ｔ kｎRcＡＱｋｅdDｘＫＪｖＳｏＦvuFxSＢＱjnhPｑ l E ｘWｆQＣ ＲＭ tI Ｇ AｖhＯＳeEＤLＱ qＶ D ｅｚＴr Cｌｃ ＪRe eｋ.

ＦｖｙＲnVP IＶＹQｚAｗJＰＧＪYZ ＢSｙｅＣ BｂＭＯmｓｃＰＴ ｉ E ｋU EＶGoueｗＧ ｓ ｊaＵｓJＪSＹＭｑＫ Lｆd DP ＯWｌ ｃxLＪｃＮWw H oaｕｕＹＸfｂＹｆＯＶ Uｉｔr yｎｑJ Xp ｃＢe GｒＤ lＺ ch Oｇｅ Y m N ｉbａｍIＥz I ｌ DAｍWgFｚQYＲＹWQKQｖf sl Ｒ ｅf ｍiＴGｃ UfP V BＤsn GｘＱCBX ｚoＢfGrL Ax ｘｏKＱｍaＸＸkO ｘｓｏrtＹT ｗＤｍb Ｒ tｘ bｋuHＬ V pCF eX ｕｔｈＥ Rｙ ＵＪIｈvK.
			</textarea>
			<input type="submit" />
		</form>
EOF;

$SUSPECTS=array();
/*
$ORIGINAL="STOP DDOSING AT!";
$SUSPECTS[]='SＴ0ｐ ＤＤ0ｓＩng Aｔ!';
$SUSPECTS[]='SＴoP Dｄ0Ｓ1ｎG ａT!';
$SUSPECTS[]='SｔｏP DD0ｓｉＮ9 @ｔ!'; 
$SUSPECTS[]='St0Ｐ dＤOsIｎ9 ＡT!';
$SUSPECTS[]='sＴＯp DＤ0ｓINg ａ+!';
$SUSPECTS[]='ｓTｏp DＤ0ｓ|Ｎ9 AＴ!'; 
$SUSPECTS[]='S+ｏp DｄｏSｉnG Ａｔ!';
$SUSPECTS[]='S+Oｐ ＤDＯｓIｎｇ AT!';
$SUSPECTS[]='ｓ+ｏＰ DｄosｉＮＧ at!';
*/
/*$ORIGINAL  ="BTW, HERE'S THE TRUE COLORS OF YOUR GLORIOUS HERO CHRISTOPHER POOLE: HTTP://WWW.ANONTALK.COM/DUMP/MOOTARD.TXT";
$SUSPECTS[]='ｂtW, ＨＥＲ3'."'".'S ｔHＥ TＲUe CＯloRs OF ＹOｕr ＧＬＯｒiＯUs ｈ3Ｒo <HrｉsｔoPｈＥr ＰＯｏL3: HＴTP://Wwｗ.@ｎｏＮt@LK.{0M/DUＭｐ/ＭｏOtAｒD.Ｔｘt';
*/
$ORIGINAL=$_GET['input'];

echo "<h2>Debugging</h2><p>Using ".mb_detect_encoding($ORIGINAL)." encoding.</p>\n";
// ISO-8859-1
echo "<h2>Original Text</h2><p>".OutputWithLineNumbers($ORIGINAL)."\n";
echo "<h2>Defucked Text</h2>".OutputWithLineNumbers(defuck_comment($ORIGINAL))."\n";
echo "<h2>Randomness Score: ".GetRandomScore(defuck_comment($ORIGINAL))."</h2>";
$rm=var_export(GatherReplacements($ORIGINAL),true);
//file_put_contents('replacement_matrix.php','<'.'?php'.$rm);
//echo "<h2>Replacement Matrix</h2><pre>".htmlspecialchars($rm)."</pre>\n";
function ordUTF8($c, $index = 0, &$bytes = null)
{
  $len = strlen($c);
  $bytes = 0;

  if ($index >= $len)
    return false;

  $h = ord($c{$index});

  if ($h <= 0x7F) {
    $bytes = 1;
    return $h;
  }
  else if ($h < 0xC2)
    return false;
  else if ($h <= 0xDF && $index < $len - 1) {
    $bytes = 2;
    return ($h & 0x1F) <<  6 | (ord($c{$index + 1}) & 0x3F);
  }
  else if ($h <= 0xEF && $index < $len - 2) {
    $bytes = 3;
    return ($h & 0x0F) << 12 | (ord($c{$index + 1}) & 0x3F) << 6
                             | (ord($c{$index + 2}) & 0x3F);
  }          
  else if ($h <= 0xF4 && $index < $len - 3) {
    $bytes = 4;
    return ($h & 0x0F) << 18 | (ord($c{$index + 1}) & 0x3F) << 12
                             | (ord($c{$index + 2}) & 0x3F) << 6
                             | (ord($c{$index + 3}) & 0x3F);
  }
  else
    return false;
}
/*
foreach($SUSPECTS as $line)
{
	$line=simplify_enc($line);
	echo htmlspecialchars("\n'$ORIGINAL'\n'$line'");
	for($i=0;$i<strlen($line);$i++)
	{
		$o=simplify_enc($ORIGINAL[$i]);
		$c=strtoupper($line[$i]);

		if($c!=$o)
		{	
			if($c!='' && $c!=' ' && (!array_key_exists($o,$CONVERSIONS) || !in_array($c,$CONVERSIONS[$o])))
				$CONVERSIONS[$o][]=$c;
		}
	}
}
*/
//?.><.? var_export($CONVERSIONS) ?.></pre>

require('includes/footer.php');
