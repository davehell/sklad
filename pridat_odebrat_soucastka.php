<?php
/**
 * pridat_odebrat_soucastka.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
session_start();

if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
  $SRBD=spojeniSRBD();
}


////////////////////////////////////////////////////////////////////////////////
// pridavani soucastky
////////////////////////////////////////////////////////////////////////////////
if(!isset($_GET["odebrat"])) {
  if (!session_is_registered('promenneFormulare')) { // musí existovat registrace kontextu promìnných formuláøe
    session_register('promenneFormulare');
  }
  foreach($_POST as $jmenoPromenne => $hodnota) { // promìnné formuláøe jsou pøedávány pøes POST
    $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
  } // foreach

  $nazev = $_SESSION['promenneFormulare']["nazev"];                //soucastka
  $cv = $_SESSION['promenneFormulare']["cv"]; //cislo vykresu      //soucastka
  $mnozstvi = $_SESSION['promenneFormulare']["mnozstvi"];                  //soucastka
  $celek = odstraneniEscape($_POST["id"], 100);                    //celek


  $vysledek = MySQL_Query("SELECT id FROM zbozi WHERE nazev='$nazev' AND c_vykresu='$cv'", $SRBD) or Die(MySQL_Error());
  $data = @MySQL_Fetch_Array($vysledek);
  $soucastka = $data["id"];


  //kontroly
  $korektniParametry = true;
  //mnozstvi
  if ((!ereg("[0-9]", $mnozstvi)) || // limit muze obsahovat pouze cislice
      ($mnozstvi < 0)) { //a nesmi byt zaporny
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['spatneKusy'];
    $korektniParametry = false;
  }


  if (! $korektniParametry)  { // byly chyby
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
  }

  $mnozstvi = str_replace(",", ".", $mnozstvi);
  MySQL_Query("INSERT INTO sestavy (id, celek, soucastka, mnozstvi) VALUES (0, '$celek', '$soucastka', '$mnozstvi')", $SRBD);// or Die(MySQL_Error());
  if (mysql_errno() != 0) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['soucastkaDuplicitni'];
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
  }
  else { //vse v poradku
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['soucastkaOK'];
    session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
    header('Location: '.$_SERVER['HTTP_REFERER'].'#soucasti-vyrobku');
    exit;
  }
}//if(!isset($_GET["odebrat"]))
////////////////////////////////////////////////////////////////////////////////
// odebirani soucastky
////////////////////////////////////////////////////////////////////////////////
else {
  if(isset($_GET["celek"])) {$celek = $_GET["celek"];}
  $soucastka = $_GET["odebrat"];

  $vysledek = MySQL_Query("SELECT id FROM sestavy WHERE soucastka='$soucastka' AND celek='$celek'", $SRBD) or Die(MySQL_Error());
  if(mysql_num_rows($vysledek) == 1) { //vse v poradku
    MySQL_Query("DELETE FROM sestavy WHERE soucastka='$soucastka' AND celek='$celek'", $SRBD) or Die(MySQL_Error());
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['soucastkaOdebratOK'];
    session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
  }
  else { //nastala chyba
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['soucastkaOdebratChyba'];
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
  }
}
?>
