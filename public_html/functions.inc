<?php
require_once('../include/accountdata.inc'); //Use this so we can login

//Definition of global variables
$wdapiurl='https://www.wikidata.org/w/api.php?';

function wdlogin(&$wduser,&$wdpass)
{
	global $wdapiurl;
	$URL2=$wdapiurl."action=login&format=xml";
	$postdata=array("lgname" => $wduser,"lgpassword" => $wdpass );
	$post=http_build_query($postdata);
	$login1=simplexml_load_string(invokeURL($URL2,TRUE,1,FALSE,$post));
	$expr="/api/login[@result='NeedToken']";
	$login2=$login1->xpath($expr)[0]->attributes()->token;
	$postdata=array("lgname" => $wduser,"lgpassword" => $wdpass,"lgtoken" =>(string) $login2 );
	$post=http_build_query($postdata);
	$login=simplexml_load_string(invokeURL($URL2,TRUE,1,FALSE,$post));
	if((string) $login->xpath("/api/login[@result]")[0]->attributes()->result=="Success")
	{
		return "Succes";
	}
	else
	{
		trigger_error((string) $login->xpath("/api/login[@result]")[0]->attributes()->result);
	}
}

function invokeURL ($url, $trans=FALSE,$post=0,$close=TRUE,$postdata="")
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, $trans);
	curl_setopt($ch, CURLOPT_POST, $post);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Mbch/2.0.0.9');
        curl_setopt ($ch, CURLOPT_COOKIEFILE, 'cookiefile.tmp');
        curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookiefile.tmp');
	if($post==1)
	{
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	}
	$jsontext=curl_exec($ch);
	if (!$jsontext)
	{
		if(curl_errno($ch)==3)
		{
			trigger_error(curl_error($ch).": $url");
		}
		trigger_error(curl_error($ch));
	}
	else
	{
		return $jsontext;
	}
	if ($close)
	{
		curl_close($ch);
	}
}
function getPropertyId($propname,$type='property')
{
	if($propname=="Label")
	{
		return "L??"; //L = label, ?? = language unknown
	}
	elseif ($propname=="Description")
	{
		return "D??"; //D = description, ?? = language unknown
	}
	elseif ($propname=="Alias")
	{
		return "A??"; //A = alias, ?? = language unknown
	}
	else
	{
		global $wdapiurl;
		$apiQuery='action=wbsearchentities&format=json&search='.$propname.'&language=nl&type='.$type;
		$jsonresult=invokeURL($wdapiurl.$apiQuery,$trans=TRUE);
		$jsonvalue=json_decode($jsonresult);
		return $jsonvalue->search[0]->id;
	}
}

function getDataType($propid)
{
	global $wdapiurl;
	if ($propid=="L??"||$propid=="D??"||$propid=="A??")
	{
		return "string";
	}
	else
	{
		$apiQuery='action=wbgetentities&format=json&ids='.$propid.'&props=datatype';
        	$jsonresult=invokeURL($wdapiurl.$apiQuery,$trans=TRUE);
		$jsonvalue=json_decode($jsonresult);
 		return $jsonvalue->entities->$propid->datatype;
	}
}

function formatValue(&$value, $datatype)
{
	switch ($datatype)
	{
		case 'string':
		return '"'.$value.'"'; //A string needs to be formatted as "some value"
		break;
		case 'monolingualtext':
		return '??:"'.$value.'"'; //A monolingualtext needs to be formatted as languagecode:"somevalue"
		break;
		case 'wikibase-item':
		return getPropertyID($value,'item'); //We need the QID
		break;
		case 'time': //Need to write the code for this type
		try {$datum=new DateTime($value);} catch (Exception $e) {}
		if(isset($datum))
		{	
			$dat="+0000000".$datum->format("Y-m-d")."T00:00:00Z/11"; // Format: +00000001840-01-01T00:00:00Z/09
			return $dat;
		}
		else
		{
			return "";
		}
		break;
		default:
		return $value; //No formatting needed or formatting unknown
	}
}

?>
