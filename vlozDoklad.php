<?php
/**
 * pridat_zapis.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
session_start();

if (!session_is_registered('promenneFormulare')) { // musí existovat registrace kontextu promìnných formuláøe
  session_register('promenneFormulare');
}
foreach($_POST as $jmenoPromenne => $hodnota) { // promìnné formuláøe jsou pøedávány pøes POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
} // foreach

$datum = $_SESSION['promenneFormulare']["datum"];
$cDokladu = $_SESSION['promenneFormulare']["cDokladu"];
$skupina = $_SESSION['promenneFormulare']["skupina"];
$prodejniCena = $_SESSION['promenneFormulare']["prodejniCena"];
$typVyroby = $_SESSION['promenneFormulare']["typVyroby"];


$korektniParametry = checkFormData();
//
if(!$korektniParametry) //chyba ve formulari
{

  header("Location: ".$_SERVER['HTTP_REFERER']);
}
else //vse OK ve formulari
{
  if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
    $SRBD=spojeniSRBD();
  }
  
  //muze existovat jen jeden doklad typu Inventura
  if($skupina == 'Inventura') {
    $vysledekX = mysqli_query('select id from doklady where skupina="Inventura"') or Die(mysqli_error($SRBD));
    if(mysqli_num_rows($vysledekX) != 0) {
        session_register('hlaseniChyba');
        $_SESSION['hlaseniChyba'] = $texty['jenJednaInventura'];
        header("Location: " . $soubory['novyDoklad']);
        exit;
    }
  }
  $timestamp_date = strtotime($datum);
  $dotaz = "INSERT INTO doklady (id, c_dokladu, skupina, datum, prod_kategorie, typ_vyroby)
  VALUES (0, '$cDokladu', '$skupina', '$timestamp_date', ";
  if(($skupina == 'Prodej') || ($skupina == 'Rezervace'))
    $dotaz .= $prodejniCena;
  else 
    $dotaz .= "NULL";
  $dotaz .= ', ';
    if($skupina == 'Výroba')
    $dotaz .= "'$typVyroby'";
  else
    $dotaz .= "NULL";
  $dotaz .= ")";
  
  mysqli_query($SRBD, $dotaz);
  
  if (mysqli_errno($SRBD) != 0) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['NovyDokladDuplicitni'];
    header('Location: '.$soubory['novyDoklad']);
    exit;
  }
  else {
    $dotaz = "SELECT id FROM doklady WHERE c_dokladu='$cDokladu'";
    $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_error($SRBD));
    While ($data = mysqli_fetch_array($vysledek)) {
      $id = $data["id"];
    }
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['dokladVlozen'];
    session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
    header('Location: '.$soubory['dokladTransakce'].'?id='.$id);
    exit;
  }
  
  //REGISTEROKSESSION
  session_unregister("hlaseniOK");
  $_SESSION['hlaseniOK'] = $texty['dokladVlozen'];
  session_unregister("promenneFormulare");
  header("Location: ".$_SERVER['HTTP_REFERER']);
  //header("Location:".);

  
}
/**
 * kontrola validity formularovych dat
 * @return TRUE pokud je vse v poradku jinak FALSE a nastavenu SESSION s chybou 
 */ 

function checkFormData()
{
global $texty;
global $datum;
$korektniParametry = true;

//datum
if (empty($_SESSION['promenneFormulare']["datum"])) { // nazev nesmi byt prazdny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdneDatum'];
  $korektniParametry = false;
}
else if (!preg_match("/^([0-9][0-9]?).([0-9][0-9]?).([0-9]{4})$/",$_SESSION["promenneFormulare"]["datum"], $dateParts))
{  session_register('hlaseniChyba');
   $_SESSION['hlaseniChyba'] = $texty['nespravneDatum'];
   $korektniParametry = false;
}
else
{
   $datum = $dateParts[3].'-'.$dateParts[2].'-'.$dateParts[1];
}
//cDokladu
if(empty($_SESSION['promenneFormulare']["cDokladu"])) { // nazev nesmi byt prazdny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdneCDokladu'];
  $korektniParametry = false;
}


//skupina
if (empty($_SESSION['promenneFormulare']["skupina"])) { // nazev nesmi byt prazdny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdnaSkupina'];
  $korektniParametry = false;
}

//prodejni cena
if (empty($_SESSION['promenneFormulare']["prodejniCena"]) &&
    (($_SESSION['promenneFormulare']["skupina"])=='Prodej' ||
      ($_SESSION['promenneFormulare']["skupina"])=='Rezervace')) { // nazev nesmi byt prazdny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdnaProdejniCena'];
  $korektniParametry = false;
}


if (empty($_SESSION['promenneFormulare']["typVyroby"]) &&
    (($_SESSION['promenneFormulare']["skupina"])=='Výroba')) { // nazev nesmi byt prazdny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdnyTypVyroby'];
  $korektniParametry = false;
}

return $korektniParametry;
}

?>












