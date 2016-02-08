<?php
    function Error_Handle($level, $message, $file, $line) {
    global $header,$err_msg,$php_errormsg,$_SERVER;
    $lvl=array(1=>"fatale run-time fouten",2=>"run-time waarschuwingen (niet fatale fouten)",4=>"compile-time parse errors",8=>"run-time opmerkingen (minder belangrijk dan waarschuwingen)",16=>"fatale fouten die gebeuren terwijl PHP opstart",32=>"waarschuwingen (niet fatale fouten) die gegeven worden terwijl PHP opstart",64=>"fatale compile-time fout",128=>"compile-time waarschuwingen (niet fatale fouten)",256=>"door de gebruiker gegenereerde foutmelding",512=>"door de gebruiker gegenereerde waarschuwing",1024=>"door de gebruiker gegenereerde opmerking");
    $header.="From: Error Master <info@remkodekeijzer.nl>"; //Afzender vaststellen
    $header.="\r\nX-mailer: PHP/";  //PHP als e-mailprogramma vaststellen.
    $header.=phpversion();
    $header.="\r\nContent-type: text/plain; charset=iso-8859-1"; //E-mail is in Tekst-opmaak
    $header.="\r\nContent-Transfer-Encoding: 8bit";
    $header.="\r\nImportance: High";
    $header.="\r\nX-priority: 1 (High)";
    $err_msg.="Er is een fout opgetreden: $lvl[$level]\r\n";
    $err_msg.="Bestand: $file\r\n Regel: $line\r\n";
    $err_msg.="Foutmelding: $message\r\n";
    if (strstr($message,'MySQL')!="") {
    $err_msg.="MySQL-fouten: ";
    $err_msg.=mysql_error();
    $err_msg.="\r\n";}
    $err_msg.="-------------------------------------------------------------------------------";
    $err_msg.="\r\nRA: ".$_SERVER['REMOTE_ADDR']."\r\n";
    $err_msg.="REF: ";if (isset($_SERVER['HTTP_REFERER'])) {echo $_SERVER['HTTP_REFERER'];};echo "\r\n";
    $err_msg.="UA: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
    $err_msg.="SS: ".$_SERVER['SERVER_SOFTWARE']."\r\n";
    $err_msg.="HOST ".$_SERVER['HTTP_HOST']."\r\n";
    $err_msg.="-------------------------------------------------------------------------------\r\n";
    error_log($err_msg,1,'remko@remkodekeijzer.nl',$header);
    die("<br><span class=\"unnamed1\">Er is een fout op deze pagina opgetreden. De beheerder is al ingelicht.</span>");
}
    $old_error_handler = set_error_handler("Error_Handle");
?>
