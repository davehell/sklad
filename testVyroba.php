<?php

header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
include "iTransakceFunkce.php";

$nedostatek = array();
$reserved = array();

session_start();
$potrebnaPrava = ZAMESTNANEC;
kontrolaPrihlaseni();
kontrolaPrav($potrebnaPrava);
$SRBD=spojeniSRBD();

if (!session_is_registered('promenneFormulare')) { // musí existovat registrace kontextu promìnných formuláøe
  session_register('promenneFormulare');
}
foreach($_GET as $jmenoPromenne => $hodnota) { // promìnné formuláøe jsou pøedávány pøes POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
} // foreach


$nazev = $_SESSION['promenneFormulare']["nazev"];
$cv = $_SESSION['promenneFormulare']["cv"];
$mnozstvi = $_SESSION['promenneFormulare']['mnozstvi'];

$korektniParametry = checkFormData("Výroba");
if(!$korektniParametry) //chyba ve formulari
{
  header("Location: ".$_SERVER['HTTP_REFERER']);
}
else //vse OK ve formulari
{ 
    if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
      $SRBD=spojeniSRBD();
    }
    
    // kontrola existence c_vykresu a nazvu
    $dotaz = "SELECT celek FROM sestavy as S join zbozi as Z on Z.id=S.celek
     WHERE nazev='$nazev' AND c_vykresu='$cv'";
    echo $dotaz;
    $vysledek = MySQL_Query($dotaz, $SRBD);
    if(mysql_num_rows($vysledek) == 0) {   //chyba
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['neniVyrobek'];
      header("Location: ".$_SERVER['HTTP_REFERER']);
      exit;
    }
    //vseOK
    else {
      $dotaz = "SELECT id FROM zbozi as Z
      WHERE nazev='$nazev' AND c_vykresu='$cv'";
      $vysledek = MySQL_Query($dotaz, $SRBD);

      While ($data = @MySQL_Fetch_Array($vysledek)) {
        $id = $data["id"];
      }
      
      $stupen_zanoreni = 1;   // stupen zanoreni 1
      if(!lzeVyrobit2($id, $mnozstvi, $stupen_zanoreni))
      {   
          //echo 'BBB';
          session_register('hlaseniChyba');
          session_register('nedostatek');
          $_SESSION['nedostatek'] = $nedostatek;
          $_SESSION['hlaseniChyba'] = $texty['nejsouSoucastky'];
      }
      else
      {   
          //echo 'AAA';
          session_register('hlaseniOK');
          $_SESSION['hlaseniOK'] = $texty['lzeVyrobit'];
          session_unregister('promenneFormulare');
      }
      header("Location: ".$_SERVER['HTTP_REFERER']);
      exit; 
    }      
    header("Location: ".$_SERVER['HTTP_REFERER']);

}




?>
