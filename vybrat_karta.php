<?php
/**
 * vybrat_karta.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
session_start();


foreach($_POST as $jmenoPromenne => $hodnota) { // prom�nn� formul��e jsou p�ed�v�ny p�es POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
} // foreach

$nazev = $_SESSION['promenneFormulare']["nazev"];                //soucastka
$cv = $_SESSION['promenneFormulare']["cv"]; //cislo vykresu      //soucastka

session_unregister('promenneFormulare');  // zru�en� kontextu formul��e
header('Location: '.$_SERVER['HTTP_REFERER'].'?nazev=');
exit;


?>
