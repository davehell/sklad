<?php
/**
 * odebratDoklad.php
 *
 */

header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";

session_start();

if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
  $SRBD=spojeniSRBD();
}

if(!isset($_GET['odebrat']))
{
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['ChybaMazaniDokladu'];
  header("Location: ".$soubory['editDoklad']);
  exit;
}


$id = $_GET['odebrat'];

//obsahuje doklad nejake polozky?
$dotaz = "SELECT id FROM transakce WHERE id_dokladu='$id'";
$vysledek = mysqli_Query($dotaz,$SRBD);

if (mysqli_num_rows($vysledek) != 0) { //doklad obsahuje polozky - nemuze byt smazan
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neprazdnyDoklad'];
    header("Location: ".$soubory['dokladTransakce']."?id=".$id);
    exit;
}
else
{//doklad je prazdny - muze byt smazan
    $dotaz = "DELETE FROM doklady WHERE id='$id'";
    $vysledek = mysqli_Query($dotaz,$SRBD);

    if (mysqli_errno() != 0) { 
     session_register('hlaseniChyba');
     $_SESSION['hlaseniChyba'] = $texty['ChybaMazaniDokladu'];
    }
    else
    {
     session_register('hlaseniCK');
     $_SESSION['hlaseniOK'] = $texty['MazaniDokladuOK'];
    }
}

header("Location: ".$soubory['editDoklad']);
exit;

?>
