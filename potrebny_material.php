<?php
/**
 * potrebny_material.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();


//kontrola, jestli existuje karta s id, ktere je hodnotou parametru
if(isset($_GET["id"])) {
  $id = $_GET["id"];
  $vysledek = mysqli_Query("SELECT nazev, c_vykresu FROM zbozi WHERE id='$id'", $SRBD) or Die(mysqli_Error());
  if(mysqli_num_rows($vysledek) != 1) { //zadane id neni v DB
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neexistujiciKarta'];
    header('Location: '.$soubory['nahledKarta']);
    exit;
  }
  else {//pozadovana karta se nachazi v DB
    $data = @mysqli_Fetch_Array($vysledek);
    $nazev = $data["nazev"];
    $cv = $data["c_vykresu"];
  }
}


uvodHTML("potrebnyMaterialNadpis");
echo '
<h1>'.$texty["potrebnyMaterialNadpis"].'</h1>

<p>
  <a href="'.$soubory["nahledKarta"].'?id='.$_GET["id"].'" class="previous">'.$texty["zpetNaKartu"].'</a>
</p>

<dl>
  <dt>'.$texty["nazev"].':</dt><dd>'.$nazev.'</dd>
  <dt>'.$texty["cv"].':</dt><dd>'.$cv.'</dd>
</dl>

<h2>'.$texty["potrebnyMaterial"].'</h2>
';
zobrazitHlaseni();

vypisSoucastky($id, "nahled");
konecHTML();
?>
