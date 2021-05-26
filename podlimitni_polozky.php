<?php
/**
 * karta_upravit.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();

uvodHTML("podlimitniTitulek");
echo
'<h1>'.$texty["podlimitniPolozky"].'</h1>';
zobrazitHlaseni();

echo '
<!-- xxx -->
<p>'.$texty["podlimitniTitle"].'</p>';

$vysledek = MySQL_Query("SELECT nazev, c_vykresu, mnozstvi, min_limit FROM zbozi WHERE mnozstvi<min_limit", $SRBD) or Die(MySQL_Error());
if(mysql_num_rows($vysledek) == 0) { //nic neni pod limitem
  echo '
  <p><strong>'.$texty["nicPodLimit"].'</strong></p><br />';
}
else { //existuji nejake podlimitni polozky
  $sudyRadek = false;
  $sloupce = array('nazev','cv','kusy', 'limit');
  echo '
<table>';
  printTableHeader($sloupce,"id=".$idZbozi);

  While ($data = MySQL_Fetch_Array($vysledek)) {
    if($sudyRadek) {
      echo '
  <tr class="sudyRadek">';
    } //if sudyradek
    else {
      echo '
  <tr>';
    } //else
    echo '
    <td>'.$data["nazev"].'</td>
    <td>'.$data["c_vykresu"].'</td>
    <td>'.$data["mnozstvi"].'</td>
    <td>'.$data["min_limit"].'</td>
  </tr>';
      $sudyRadek = !$sudyRadek;
    } //while
    echo '
</table>';
}

konecHTML();
?>
