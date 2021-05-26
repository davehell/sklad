<?php
/**
 * koeficient.php
 *
 * Obsahuje - formular pro zadani koeficient
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ADMIN;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);

$SRBD = spojeniSRBD();

$vysledek = MySQL_Query("SELECT hodnota FROM koeficienty WHERE id = 1", $SRBD);
$data = MySQL_Fetch_Array($vysledek);
$hodnota = $data["hodnota"];

if($_POST) {
  //hodnota
  if (isset($_POST['hodnota'])) { // promenna hodnota je zadana
    $hodnota = odstraneniEscape($_POST['hodnota'], 100);
  }


  //kontroly
  $korektniParametry = true;
  //hodnota
  if (strlen($hodnota) <= 0) { 
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['spatnaHodnota'];
    $korektniParametry = false;
  } 


  if (! $korektniParametry)  {    // parametry nebyly naèteny korektnì
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
  }  // if !korektni parametry

  $hodnota = str_replace(",", ".", $hodnota);//pripadne desetinne carky nahradi za tecky
  MySQL_Query("UPDATE koeficienty SET hodnota='$hodnota' WHERE id=1", $SRBD) or Die(MySQL_Error());
  session_register('hlaseniOK');
  $_SESSION['hlaseniOK'] = $texty['editOK'];
  //header('Location: '.$_SERVER['HTTP_REFERER']);
  //exit;
} //if($_POST)



uvodHTML("koeficienty", "admin");
echo "<h1>".$texty["koeficienty"]."</h1>\n";
echo "<h2>".$texty["editovatKoeficienty"]."</h2>\n";
?>
<form method="post" action="">
<?php zobrazitHlaseni(); ?>
<fieldset>
  <legend><?php echo $texty['nazevKoeficientu']; ?></legend>
  <label for="hodnota"><?php echo $texty['hodnotaKoeficientu']; ?>:</label>
  <input type="text" size="30" maxlength="10" id="hodnota" name="hodnota" value="<?php echo $hodnota; ?>" /><br />
<br />
<?php echo dejTlacitko('odeslat','ulozitZmeny'); ?>
</fieldset>
</form>
<?php

konecHTML();
?>
