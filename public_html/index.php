<?php
require_once('header.inc');
if (!isset($_POST)||empty($_POST)) //No information was submitted, so need to show form
{
	?>
	<!DOCTYPE html>
	<html>
	<head>
	<title>QuickStatements Generator</title>
	<meta charset="UTF-8">
	<meta name="description" content="Tool to generate statements to use in QuickStatements based on tab based text">
	</head>
	<body>
	<p id="information">Currently only English and Dutch property names and item names (if they need to be translated into QID's) are accepted. Dates need to be in format: d-m-Y or Y-m-d (no trailing zeros needed, but no problem if they are added. First row needs to be the properties, other rows need to be the statements. It's allowed to use item id and property id, they will return unaltered.<br><small>If no language is specified the default used language will be Dutch. Request additional languages on <a href="https://github.com/mbch331/QSGenerator/issues" target="_blank" rel="nofollow">GitHub</a>.</small></p>
	<form action="index.php" method="POST" enctype="multipart/form-data">
	<label>Which language do you want to search in: <input type="text" value="" placeholder="Dutch" name="lang"></label><br>
	<label>Paste the text you want to use:<br>
	<textarea name="data" rows="6" cols="100" placeholder="Some tab seperated text with a new line for each data row" wrap="soft"></textarea></label>
	<input type="hidden" name="verzonden" value="1">
	<input type="submit" value="Verwerk">
	</form>
	</body>
	</html>
	<?php
}
else
{
	require_once('functions.inc'); // Store the functions in a separate file
	//Form was submitted so needs to be processed
	//Determine what language to use
	if (!empty($_POST['lang']))
	{
		try {$json_file=fopen('lang.json','r');} catch (Exception $e) {trigger_error('Taalbestand niet te lezen');}
		$json_text=fread($json_file,filesize('lang.json'));
		$json=json_decode($json_text);
		if (isset($json->$_POST['lang']))
		{
			$lang=$json->$_POST['lang'];
		}
		else
		{
			$lang='nl';
		}
	}
	else
	{
		$lang="nl";
	}
	//Step 1 processing: creating rows
	$row=explode("\r\n",trim($_POST['data']));
	$n=0; //Start with row 0 for array $pair
	for($i=0;$i<count($row);$i++)
	{
		//Check for empty rows and do nothing
		$txt=preg_filter("/(\t)+/i","",$row[$i]);
		$nt=strlen($txt); //regular expressions fail at non-latin script
		if($nt>0)
		{
			//Step 2 processing: create cells from rows
			$cell[$i]=explode("\t",$row[$i]);
			if($i==0) // Row 0 contains the properties
			{
				for($j=0;$j<count($cell[0]);$j++)
				{
					$property[$j]['id']=getPropertyId($cell[0][$j]); //Get the property ID from Wikidata
				}
			}
			else
			{
				for($j=0;$j<count($cell[$i]);$j++)
				{
					$key=$property[$j]['id'];
					$value=$cell[$i][$j];
					$pair[$n][$key]=$value;
				}
			}
			$n++; //$n should only be raised when $pair[$n] has been filled
		}
	}
	echo '<code>';
	for ($i=1;$i<=count($pair);$i++)
	{
		echo "CREATE<br>\r\n";
		foreach($pair[$i] as $key => $value)
		{
			if($value!="")
			{
				echo "LAST\t$key\t".formatValue($value,getDataType($key))."<br>\r\n";
			}
		}
	}	
	echo '</code>';
}
?>
