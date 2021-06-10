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

  $dotaz = "SELECT id FROM zbozi WHERE nazev='$nazev' AND c_vykresu='$cv'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
  $data = @mysqli_fetch_array($vysledek);
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
  $dotaz = "INSERT INTO sestavy (id, celek, soucastka, mnozstvi) VALUES (0, '$celek', '$soucastka', '$mnozstvi')";
  mysqli_query($SRBD, $dotaz);// or Die(mysqli_Error());
  if (mysqli_errno($SRBD) != 0) { //vkladan duplicitni zaznam
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

  $dotaz = "SELECT id FROM sestavy WHERE soucastka='$soucastka' AND celek='$celek'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
  if(mysqli_num_rows($vysledek) == 1) { //vse v poradku
    $dotaz = "DELETE FROM sestavy WHERE soucastka='$soucastka' AND celek='$celek'";
    mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
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
