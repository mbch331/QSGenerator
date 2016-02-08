<?php
require_once('../include/accountdata.inc'); //Use this so we can login

//Definition of global variables
$wdapiurl='https://www.wikidata.org/w/api.php?';

function wdlogin(&$wduser,&$wdpass)
{
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
function getPropertyId($propname)
{
	$apiQuery='action=wbsearchentities&format=json&search='.$propname.'&language=nl&type=property';
	$jsonresult=invokeURL($wdapiurl.$apiQuery);
	var_dump($jsonresult);
}
?>
