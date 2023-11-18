<?php

/**
 * Stéphane Wouters - 2009
 */

require('mysqli_fix.php'); // 2023 fix compatibilité PHP 5.4 -> 7

session_start();

$ip = $_SERVER["REMOTE_ADDR"];
$db = mysql_connect("mysql","root","root");
mysql_select_db("doewar");

if(!mysql_query("DESCRIBE `accounts`")) {
    mysql_query("
        CREATE TABLE IF NOT EXISTS `accounts` (
        `ID` int(11) NOT NULL auto_increment,
        `login` text,
        `mdp` text,
        `email` text,
        `age` text,
        `class` tinyint(4) default '1',
        `points` int(11) NOT NULL,
        `level` int(11) NOT NULL default '1',
        `lastUpdate` varchar(255) NOT NULL,
        `attaques` int(11) NOT NULL default '1',
        PRIMARY KEY  (`ID`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;
    ");
    mysql_query("
        CREATE TABLE IF NOT EXISTS `attaques` (
        `ID` int(11) NOT NULL auto_increment,
        `de` text,
        `pour` text,
        `tpsDepart` text,
        PRIMARY KEY  (`ID`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;
    ");
    mysql_query("
        CREATE TABLE IF NOT EXISTS `cout_lanceurs` (
        `ID` int(11) NOT NULL auto_increment,
        `level` int(11) default NULL,
        `cout` int(11) default NULL,
        PRIMARY KEY  (`ID`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;
    ");
    mysql_query("
        INSERT INTO `cout_lanceurs` (`ID`, `level`, `cout`) VALUES
        (1, 3, 600000),
        (2, 4, 1500000),
        (3, 5, 4200000),
        (4, 6, 123456789),
        (6, 7, 2147483647);
    ");
}

function DateToSec($date=''){
if(empty($date)) return 0;
return @mktime(substr($date,11,2),substr($date,14,2),substr($date,17,2),
substr($date,3,2),substr($date,0,2),substr($date,6,4));
}

function GetDiffDate($date1='',$date2=''){
return abs(DateToSec($date1)-DateToSec($date2));
}

function update()
{
	$repUPDATE = mysql_query("SELECT * FROM accounts ORDER BY ID");
	while ($donUPDATE = mysql_fetch_array($repUPDATE))
	{
		$idCompte = $donUPDATE['ID'];
		$level = $donUPDATE['level'];
		$lastUpdate = $donUPDATE['lastUpdate'];
		$dateActuel = date("d/m/Y H:i:s");
		$tempsPasse = GetDiffDate($lastUpdate,$dateActuel);
		$periodePasses = $tempsPasse / 15;
		if (is_int($periodePasses) == $periodePasses)
		{
			$gain = ($level*$level)*$periodePasses;
			$new_points = $gain + $donUPDATE['points'];
			mysql_query("UPDATE `accounts` SET `lastUpdate`='$dateActuel', `points`='$new_points' WHERE (`ID`='$idCompte')");
		}

	}
}

function luanchAtks()
{
	$repAttaques = mysql_query("SELECT * FROM attaques");
	while ($donAttaques = mysql_fetch_array($repAttaques))
	{
		$tpsDepart = $donAttaques['tpsDepart'];
		$cible = $donAttaques['pour'];
		$de = $donAttaques['de'];
		$idAtk = $donAttaques['ID'];
		$dateActuel = date("d/m/Y H:i:s");
		$tempsPasse = GetDiffDate($tpsDepart,$dateActuel);
		if ($tempsPasse >= 600)
		{
				$infoCible = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE login='$cible'"));
				$ptsCible = $infoCible['points'];
				$infoAttaquant = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE login='$de'"));
				$ptsDe = $infoAttaquant['points'];
				$nouveauxPoints = $ptsCible + $ptsDe;
				mysql_query("UPDATE accounts SET points='0' WHERE (login='$cible')");
				mysql_query("UPDATE accounts SET points='$nouveauxPoints' WHERE (login='$de')");
				mysql_query("DELETE FROM attaques WHERE (ID='$idAtk')");
		}

	}
}


function decom($nombre)
{
	$milliards = floor($nombre/1000000000); if ($milliards == 0){$milliards = '';} else {$milliards = $milliards." ";}
	$millions = substr(floor($nombre/1000000), -3); if ($millions == 0){$millions = '';} else {$millions = $millions." ";}
	$milliers = substr(floor($nombre/1000), -3); if ($milliers == 0){$milliers = '';} else {$milliers = $milliers." ";}
	$unites = substr(floor($nombre), -3);
	$result = $milliards.$millions.$milliers.$unites;
	return $result;
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<style type="text/css">
@font-face
{font-family:Police personnelle;
src:url(/assets/police.ttf);
}
html, body{
    height:100%;
}
/*
///////////////
*/
.policeDesign1 {
    color: rgb(0, 0, 0);
    font-family: Tahoma;
}
.policeDesign2 {
    color: rgb(220, 220, 220);
    font-family: Tahoma;
}

</style>
<title>DoeWar game</title>
<meta content="text/html; charset=utf-8" http-equiv="content-type">
</head>
<body style="font-family:Police personnelle; color: rgb(220, 220, 220); background-image: url(/assets/fond.jpg); background-attachment: fixed; background-position: center bottom; height:100%;" bottommargin="0" topmargin="0" leftmargin="0" rightmargin="0" marginheight="0" marginwidth="0" alink="#fffffe" link="#fffffe" vlink="#fffffe">
<?php


$trop = 0;
luanchAtks();

if (isset($_GET['deco'])){ // Si une déconnexion est faite
session_destroy(); ?><meta http-equiv="refresh" content="0; URL=index.php"><?php }


if (isset($_GET['new']))
{
	$NEW_login = htmlspecialchars($_POST['NEW_login'], ENT_QUOTES);
	$NEW_mdp = htmlspecialchars($_POST['NEW_mdp'], ENT_QUOTES);
	$donN = mysql_fetch_array(mysql_query("SELECT * FROM accounts WHERE login='$NEW_login'"));
	$dateActuel = date("d/m/Y H:i:s");
	if (empty($donN['login']))
	{
		mysql_query("INSERT INTO `accounts` (`login`, `mdp`, `points`, `level`, `lastUpdate`) VALUES ('$NEW_login', '$NEW_mdp', 2, 1, '$dateActuel')");
	}
}
if (isset($_GET['login']))
{
	$login = htmlspecialchars($_POST['login'], ENT_QUOTES);
	$mdp = htmlspecialchars($_POST['mdp'], ENT_QUOTES);
	$reponse = mysql_query("SELECT * FROM accounts WHERE login='$login'");
	$donnees = mysql_fetch_array($reponse);
	if ($donnees['mdp'] == $mdp){$_SESSION['compte'] = $login;}
}
if (isset ($_SESSION['compte']))
{
	?><center><table style="background-color: rgb(25, 25, 25); width:800px;">
	<tr><td style="background-image: url(/assets/top.jpg); height: 180px; text-align: right; vertical-align: bottom; color: rgb(0, 0, 0); "><?php echo date("H:i:s"); ?></small></td></tr>
	<tr><td style="text-align: center; ">
	<p style="font-family:Police personnelle">
	<?php

	$compte = $_SESSION['compte'];

	if (isset ($_POST['msg']))
	{
		$message = htmlspecialchars($_POST['msg'], ENT_QUOTES);
		$heure = date("G:i:s");
		mysql_query("INSERT INTO tchat (`login`, `msg`, heure) VALUES ('$compte', '$message', '$heure')");
	}

	if (isset ($_GET['esquive'])){$idATK = $_GET['esquive']; mysql_query("DELETE FROM `attaques` WHERE (`ID`='$idATK')");}

	$reponse = mysql_query("SELECT * FROM accounts WHERE login='$compte'");
	$donnees = mysql_fetch_array($reponse);

	$lanceurPlus1 = $donnees['attaques'] + 1;
	$donCoutLanc = mysql_fetch_array(mysql_query("SELECT cout FROM cout_lanceurs WHERE level='$lanceurPlus1'"));
	$coutLanceur = $donCoutLanc['cout'];

	if (isset ($_GET['atkPlus']))
	{
		if ($donnees['points'] >= $coutLanceur)
		{
			$new = $donnees['points'] - $coutLanceur;
			mysql_query("UPDATE `accounts` SET `points`='$new', `attaques`='$lanceurPlus1' WHERE (`login`='$compte')");
			?><meta http-equiv="refresh" content="0; URL=index.php"><?php
		}
		else
		{
			echo '<meta http-equiv="refresh" content="0; URL=index.php">';
		}
	}
	if (isset ($_GET['levelPlus']))
	{
		$newLevel = $donnees['level'] + 1;
		$reponse = mysql_query("SELECT * FROM accounts WHERE login='$compte'");
		$donnees = mysql_fetch_array($reponse);
		$valeur = ($donnees['level']*$donnees['level']*($donnees['level']/2))+1;
		if ($donnees['points'] >= $valeur)
		{
			$new = $donnees['points'] - $valeur;
			mysql_query("UPDATE `accounts` SET `points`='$new', `level`='$newLevel' WHERE login='$compte'");
			?><meta http-equiv="refresh" content="0; URL=index.php"><?php
		}
		else
		{
			echo '<meta http-equiv="refresh" content="0; URL=index.php">';
		}
	}

	$ip = $_SERVER["REMOTE_ADDR"];
	$newActu = ($donnees['actualisations'] + 1);
	mysql_query("UPDATE accounts SET actualisations='$newActu' WHERE login='$compte'");
	mysql_query("UPDATE accounts SET ip='$ip' WHERE login='$compte'");

	if (isset ($_GET['attaquer']))
	{
		$repAK = mysql_query("SELECT * FROM attaques WHERE de='$compte'");
		for ($i=0; ($donAK = mysql_fetch_array($repAK)); $i++){ }
		if ($i < $donnees['attaques'])
		{
			$cible = $_GET['attaquer'];
			$cout = ($donnees['level']*$donnees['level'])*10;
			$repCible = mysql_query("SELECT * FROM accounts WHERE login='$cible'");
			$donCible = mysql_fetch_array($repCible);
			$pointNecessaire = $donCible['points'] * 2;
			if ($donnees['points'] < $pointNecessaire){$error1=1;} else {
			if ($donnees['points'] < $cout){$error2=1;} else {
			$nouveauxPoints = $donnees['points'] - $cout;
			$date = date("d/m/Y H:i:s");
			mysql_query("UPDATE `accounts` SET `points`='$nouveauxPoints' WHERE login='$compte'");
			mysql_query("INSERT INTO `attaques` (`de`, `pour`, `tpsDepart`) VALUES ('$compte', '$cible', '$date')");
			?><meta http-equiv="refresh" content="0; URL=index.php"><?php
			}}
		}
		else
		{
			$trop = 1;
		}

	}


	update();
	echo "JOUEUR $compte";
	?><br><a href='index.php'><small>Actualiser</a> - <a href='index.php?deco'>Déconnexion</a></small>
	<?php
	echo "<br><br>Vous êtes niveau ".$donnees['level'];
	echo '.<br>Vous gagnez '.decom($donnees['level']*$donnees['level'])." doelars toutes les 15 secondes.";
	?><br><br>Vous avez <span style="color: rgb(255, 255, 0);"><?php echo decom($donnees['points']) ?></span> doelars<br>
	<hr size="1">


	BOUTIQUE<br><br>
	Passer level <?php echo $donnees['level']+1 ?> pour un cout de <?php echo decom(($donnees['level']*$donnees['level']*($donnees['level']/2))+1) ?> doelars
	[<a href="index.php?levelPlus">Acheter</a>]<br>

	Acheter un <?php echo $donnees['attaques']+1 ?>éme lanceur d'attaque pour un cout de <?php echo decom($coutLanceur); ?> doelars
	[<a href="index.php?atkPlus">Acheter</a>]<br>

	<hr size="1">

	ATTAQUES<br><br>
	<?php
	if ($trop)
	{
		echo "<span style='color: rgb(255, 0, 0);'><b>- Attaque impossible -<br></b>Vous avez attein votre maximum d'attaques simultannés</span><br><br>";
	}
	$reponseATK = mysql_query("SELECT * FROM attaques WHERE pour='$compte'");
	while ($donATK = mysql_fetch_array($reponseATK))
	{
		echo '<span style="color: rgb(255, 0, 0);">Vous êtes attaqués par '.$donATK['de'].'. <a href="index.php?esquive='.$donATK[ID].'">[Esquiver lattaque]</a></span><br>';
	}
	$reponseATK = mysql_query("SELECT * FROM attaques WHERE de='$compte'");
	$i = 0;
	while ($donATK = mysql_fetch_array($reponseATK))
	{
		$dateActuel = date("d/m/Y H:i:s");
		$tpsDepart = $donATK['tpsDepart'];
		$secondesRestantes = GetDiffDate($tpsDepart,$dateActuel);
		echo "<span style='color: rgb(0, 255, 0);'>Vous êtes en train d'attaquer ".$donATK['pour'].'. Arrivée dans '.(600 - $secondesRestantes).' secondes</span><br>';
		$compteATK[$i] = $donATK['pour'];
		$i++;
	}
	if (isset($error1)){echo "<span style='color: rgb(255, 0, 0);'>Vous n'avez pas assez de points par rapport à cet adversaire (".$cible.")</span><br>";}
	if (isset($error2)){echo "<span style='color: rgb(255, 0, 0);'>Vous n'avez pas assez de points pour faire une attaque.<br></span>";}
	?>
	<?php if (isset($_GET['infoAtk'])){ ?>
	<small><br>Une attaque correspond à voler tout les points d'un joueur.<br>
	Vous pouvez attaquer un joueur à la condition d'avoir 2 fois plus de points que lui.<br>
	Le lancement d'une attaque coute <b><?php echo ($donnees['level']*$donnees['level'])*10; ?> doelars.</b><br>
	Une fois l'attaque lancée, la personne attaquée a 10 minutes pour esquiver l'attaque.<br>
	Si aucune esquive est faite au bout de 10 minutes, vous gagnez ses doelars.<br>
	Si l'esquive est faite, le defenseur ne vous prend pas de points. (Vous perdez juste le cout de l'attaque).<br>
	Vous avez actuellement le droit à <b><?php echo $donnees['attaques']; ?> attaques</b> simultannées.
	<br><br><small><a href="index.php">Masquer les infos</a></small></small>
	<?php } else { ?>
	<small><small><a href="index.php?infoAtk">En savoir plus sur les attaques</a></small></small><?php } ?>
	<hr size="1">



	MONDE<br><br><center><table style="background-color: rgb(19, 19, 19);" border="1" cellpadding="10" cellspacing="0">
	<tr><td></td><td><small>Pseudo</td><td><small>doelars</td><td><small>Niveau</td><td><small>Actus</td><td><small>Atks</td><td></td><tr>
	<?php
	$rep = mysql_query("SELECT * FROM accounts ORDER BY points DESC");
	$i = 1;
	while ($don = mysql_fetch_array($rep))
	{
		$vert = 0;
		for ($z=0; $z < count($compteATK); $z++)
		{
			if ($compteATK[$z] == $don['login'])
			{
				$vert = 1;
			}
		}
		if ($vert)
		{
			echo '<tr style="background-color: rgb(19, 40, 40);" >';
		}
		elseif ($don['login'] == $compte)
		{
			echo '<tr style="background-color: rgb(27, 27, 27);" >';
		}
		else
		{
			echo '<tr>';
		}
		?><td><?php echo $i; ?>.</td>
		<td style="vertical align=center"><small><?php echo $don['login']; ?></td>
		<td><small><?php echo decom($don['points']); ?> doelars</td>
		<td><small>niveau <?php echo $don['level']; ?></td>
		<td style="text-align: center;"><small><?php echo $don['actualisations']; ?></td>
		<td style="text-align: center;"><small><?php echo $don['attaques']; ?></td>
		<td><small><form method="post" action="index.php?attaquer=<?php echo $don['login']; ?>"><input value="Attaquer" type="submit"></form></td><?php
		$i++;
		$z++;
	}

	?></table></center><br>
	<br><hr size="1"><small>Doewar - © 2009 Stéphane Wouters
	</td></tr></table></center>
	<?php
}
else
{

	?>
	<table style="text-align: left; width: 100%; height: 100%; background-color: rgb(20, 20, 20);" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<td style="vertical-align: middle; text-align: center; ">
		<center>
		<table style="text-align: left; width: 300px; height: 170px; background-color: rgb(30, 30, 30);" cellpadding="10">
		<tr style="vertical-align: top;">
		<td>
		<div style="text-align: center;"><span style="color: rgb(150, 150, 150);">J'ai déjà un compte<br><br></div></span>

		<form method="post" action="index.php?login">
		<center><table>
		<tr><td style="width: 130px;"><span style="color: rgb(200, 200, 200);">Pseudo:</td><td>
		<input name="login" style="background-color: rgb(40, 40, 40); width: 128px; height: 20px; border: 0px none; color: white; type="text"></td></tr>
		<tr><td style="width: 130px;"><span style="color: rgb(200, 200, 200);">Mot de passe:</td><td>
		<input name="mdp" style="background-color: rgb(40, 40, 40); width: 128px; height: 20px; border: 0px none; color: white;" type="password"></td></tr>
		</table><br><input value="Connexion" type="submit"></center>

		</td></tr></table></form>
		<br><br>
		<table style="text-align: left; width: 300px; height: 170px; background-color: rgb(30, 30, 30);" cellpadding="10">
		<tr style="vertical-align: top;">
		<td>
		<div style="text-align: center;"><span style="color: rgb(150, 150, 150);">Créer un compte<br><br></div></span>
		<form method="post" action="index.php?new">
		<center><table>
		<tr><td style="width: 130px;"><span style="color: rgb(200, 200, 200);">Pseudo:</td><td>
		<input name="NEW_login" style="background-color: rgb(40, 40, 40); width: 128px; height: 20px; border: 0px none; color: white;" type="text"></td></tr>
		<tr><td style="width: 130px;"><span style="color: rgb(200, 200, 200);">Mot de passe:</td><td>
		<input name="NEW_mdp" style="background-color: rgb(40, 40, 40); width: 128px; height: 20px; border: 0px none; color: white" type="password"></td></tr>
		</table><br><input value="Inscription" type="submit"></center>
		</td></tr></table></form></center><br><br>


	</td>
	</tr>
	</table>
	<?php

}
