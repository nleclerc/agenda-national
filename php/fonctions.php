<?

function getQueryParameter($parmName){
	$value = null;
	
	if (isset($_POST[$parmName]))
		$value = $_POST[$parmName];
	else if (isset($_GET[$parmName]))
		$value = $_GET[$parmName];
	
	return $value;
}

function __autoload($class_name){
/*fonction php 5 permettant d'inclure les class sans la liste infernale*/
	require_once "class/".$class_name.".php";
}


function coloration($var){

	global $color;

	if(ereg(" ",$var)){
		$mot=explode(" ",$var);
		$nb=count($mot);
		$i=rand(0,$nb-1);
		$mot[$i]='<span class="color">'.$mot[$i].'</span>';
		for($j=0;$j<$nb;$j++){
			$phrase.=$mot[$j]." ";
		}
		return $phrase;
	}else{
		$html=explode("&",$var);
		if(count($html)>1&&!empty($var[0])) {
			$mot='<span class="color">'.$html[0].'</span>';
			for($j=1;$j<count($html);$j++){
				$mot.="&".$html[$j];
			}
			return $mot;
		}elseif(count($html)>1&&empty($var[0])) {
			return $var;
		}else{
			$nb=strlen($var);
			$nba=rand(2,$nb-2);
			$part[1]=substr($var,0,$nba);
			$part[2]=substr($var,$nba);
			$i=rand(1,2);
			$part[$i]='<span class="color">'.$part[$i].'</span>';
			return $part[1].$part[2];
		}
	}
}

/*****************************************/

function titre($var){
	
	switch($var){
	case "activites" :
		$titre="Les activit&eacute;s";
	break;
	case "tests":
		$titre="Les tests";
	break;
	case "enfants":
		$titre="Les enfants";
        break;
	case "agenda":
		$titre="L'agenda";
        break;
	default:
		$titre="Mensa";
	break;
	}
	
	return $titre;
}

/*****************************************/

function text2html($s){
	if(is_array($s)){
		$a=array();
		foreach($s as $k=>$v) $a[$k]=text2html($v);
		return $a;}
	else{
		for ($r='',$i=0;$i<strlen($s);$i++) $r.=(ord($c=substr($s,$i,1))>127)?htmlentities($c):$c;
		return str_replace("\n",'<br />',$r);
	}
}

/*****************************************/

// GetHTTP: connexion a un site web
function GetHTTP($dom, $url, $port=80, $to=9) {
	$h=''; $s='';
	if ($f = @fsockopen($dom, $port, $errno, $errstr, $to)) {
		fputs($f,
			"GET /$url HTTP/1.0\r\n".
			"Host: $dom\r\n".
			"User-Agent: Mensa/GS\r\n\r\n");
		while ((!feof($f)) && preg_match("/[^\r\n]/", $h=fgets($f, 1024))) {}
		while($l = fgets($f, 1024)) $s .= $l;
		fclose($f);
	}
	return $s;
}

/*****************************************/

?>
