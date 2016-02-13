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
	<p id="information">Currently only English and Dutch property names and Dutch item names (if they need to be translated into QID's) are accepted. Dates need to be in format: d-m-Y or Y-m-d (no trailing zeros needed, but no problem if they are added. First row needs to be the properties, other rows need to be the statements. It's allowed to use item id and property id, they will return unaltered.</p>
	<form action="index.php" method="POST" enctype="multipart/form-data">
	<textarea name="data" rows="6" cols="100"></textarea>
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
	//Step 1 processing: creating rows
	$row=explode("\r\n",trim($_POST['data']));
	for($i=0;$i<count($row);$i++)
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
				$pair[$i][$key]=$value;
			}
		}
	}
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
}
?>
