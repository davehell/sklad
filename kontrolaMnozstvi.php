<?php


header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
include_once "aktualizaceKonzistenceMnozstvi.php";

$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);

uvodHTML("kontrolaMnozstvi");
echo '
<h1 class="noPrint">'.$texty['kontrolaMnozstvi'].'</h1>';
zobrazitHlaseni();

if (!isset($SRBD)) { // uz jsme pøipojeni k databázi
  $SRBD=spojeniSRBD();
}

$ct = new constistenceTester($SRBD);
// pokud byl pozadavek na opravu databaze
if(isset($_POST['odeslano']))
{
  $ct->vypisy = "0";
  $ct->testAllAmounts();
  echo 'Opraveno';
}
$ct->echoInconsistentItems();
echo '<br />
      <form action="" method="post">
        <input type="submit" value="opravit" name="odeslano">
      </form>';
konecHTML();

session_unregister($_POST['odeslano']);

