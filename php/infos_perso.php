<?
$dom="82.224.163.111"; //mensa.accorimmo.com ; ED:82.229.183.136

if(!$web_k) echo "<h2>Erreur !</h2><p>".(($WI<1)?
"Vous n'avez pas acc&egrave;s &agrave; l'ensemble des fonctions du site.":
"La base des membres n'est pas op&eacute;rationnelle en ce moment.<br />Merci de r&eacute;essayer ult&eacute;rieurement.")."</p>";
else if($web_red=='mes_edit.shtml' && $web_info=='membre' || $ID){
	if(!$mel) $mel=$WM;
	$F=array(
	titre=>'Modification de votre fiche annuaire',
	obj=>'Modification de la fiche membre',
	intro=>"Indiquer pr&eacute;cis&eacute;ment quelles donn&eacute;es doivent &ecirc;tre modifi&eacute;es, supprim&eacute;es ou ajout&eacute;es",
	dest=>"adhesion",
	champs=>array(
		array(label=>"Nom",nom=>nom, val=>$WN, type=>text, taille=>32),
		array(label=>"Pr&eacute;nom",nom=>prenom, val=>$WP, type=>text, taille=>32),
		array(label=>"Adresse e-mail",nom=>mel, val=>$mel, opt=>1, type=>text, taille=>32),
		array(label=>"Modifications",nom=>modif, val=>$modif, type=>zone, taille=>'60x12'),
		array(label=>"Membre",nom=>ID, val=>"$WI", type=>hidden)));
	include "form.inc";}
else{
$t="4daction/web_$web?";
if($web_nom)$t.="web_nom=".urlencode($web_nom)."&";
if($web_red)$t.="web_red=$web_red&";
if($web_refact)$t.="web_refact=$web_refact&";
if($web2)$t.="web2=$web2&";
if($web_mem_rec)$t.="web_mem_rec=$web_mem_rec&";
$t.="web_message=$web_k";

if($web_k && $s=GetHTTP($dom,$t,80)){
	$s=stristr($s,'<div id="texte">');
	$s=substr($s,16,strpos($s,'</div>')-16);
	$s=str_replace('<p><span class="membres_titres">Acc&egrave;s r&eacute;serv&eacute; aux Membres</span></p>','',$s);
	$s=str_replace('<h1><br />','<h1>',$s);
//	$s=str_replace('<h1>','<h1><img class="fr" src="img/4D.gif" alt="Powered by 4D" />',$s);
	$s=preg_replace('"<tr>[\s]*<td>[\s]*</td>[\s]*</tr>[\s]*"','',$s);
	$s=preg_replace('"<tr>[\s]*<t[^>]*>[\s]*<p><i>Copyright[\s\S]*</tr>"','',$s);
	$s=preg_replace('/<table[^>]*>/','<table>',$s);
	$s=preg_replace('/align=[ "]*center["]?/','style="text-align:center"',$s);
	$s=preg_replace('/<!--[^>]*-->/','',$s);
	$s=str_replace('type="text"','type="text" class="champ"',$s);
	$s=str_replace('type="submit"','type="submit" class="bouton"',$s);

	$F=$act?"$web\t$S":''; $n=$h=$id=0; $r='DB&amp;web=decode&amp;web_red=membre.shtml&amp;web_refact';
	foreach(split("[\n\r]",$s)as $l){
		$l=trim($l);
		$l=str_replace(' action="web'," action=\"http://$dom/4daction/web",$l);
		$l=preg_replace('/<img src="([^>]*)>/','<img alt="" src="http://'.$dom.'\1 />',$l);
		$l=str_replace(' SRC="/'," alt=\"\" src=\"http://$dom/",$l);
		if(strstr($l,' href="')){
			$w="<p><i>Copyright";
			if (substr($l,0,15)==$w) $l="$w &copy; ".date("Y").' <a href="mailto:informations@mensa.fr">Mensa France</a><br />';
			$l=preg_replace('/&web_message=[A-Z]{16}/','',$l);
			$w='="?action=membres_DB&amp;web=';
			$l=str_replace("?web_","&amp;web_",$l);
			$l=str_replace("&web_","&amp;web_",$l);
			$l=str_replace('="web_',$w,$l);}
		$l=str_replace(' src="../commun',' src="img',$l);
//		if($style=="mobile") $l=str_replace('<img src="img/partenaires.gif" alt="" />','Powered by 4D',$l);
		if($id && $F){ $F.="\n$id\t$l"; $id=0;}
		if($F && preg_match('~^<td style="text-align:center">&nbsp;([A-Z]{3})</td>$~',$l,$h)) $F.="\t".$h[1];
		if($h=strpos($l,$r)){ $h+=strlen($r)+1; $id=substr($l,$h,($j=strpos($l,'">',$h))-$h); $l=str_replace($r,'rec&amp;id',$l);}
		if($l && !$F) echo "$l\n";}
if($F){ $h=fopen("tmp/m$WI",'w'); fwrite($h,$F); fclose($h);}else{?>
<p><? if($WI==2654 && $web_red=='activite.shtml' && $i=$web_refact) a("?action=activites_liste&A=I&id=$i",'>Rendre visible sur le site');?></p>
<table><tr><td class="T"><img src="img/4D.gif" alt="Powered by 4D" /></td></tr></table><?}}
else{?>
<h2>Erreur !</h2>
<p>La connexion avec la base de donn&eacute;es n'a pu s'effectuer.<br />
Merci de r&eacute;essayer ult&eacute;rieurement.</p>
<?}}?>