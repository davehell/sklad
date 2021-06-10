<?php
/**
 * pridat_upravit_karta.php
 *
 * provadi kontrolu zadanych dat
 * vlozeni noveho do DB
 * aktualizace stavajiciho zaznamu
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
session_start();

if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
  $SRBD=spojeniSRBD();
}

/*******************************************************************************
 * odstraneni karty
 ******************************************************************************/
if(isset($_GET["odebrat"])) {
  $odstranovaneID = $_GET["odebrat"];
  $dotaz = "SELECT obrazek FROM zbozi WHERE id='$odstranovaneID'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
  $data = mysqli_fetch_array($vysledek);
  
  if(mysqli_num_rows($vysledek) == 1) { //vse v poradku
    $dotaz = "DELETE FROM zbozi WHERE id='$odstranovaneID'";
    mysqli_query($dotaz, $SRBD);
    if(mysqli_errno() != 0) { //zbozi nejde odstranit
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['kartaOdebratChyba'];
      session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
      header('Location: '.$soubory['upravitKarta']."?id=".$odstranovaneID, true, 303);
      exit;
    }
    else { //vse v poradku
      if($data["obrazek"] != "") { //pokud melo zboti priprazeny obrazek
        if(File_Exists("nahledy/".$data["obrazek"])) unlink("nahledy/".$data["obrazek"]);  //vymazani souboru
        if(File_Exists("nahledy/thumb_".$data["obrazek"])) unlink("nahledy/thumb_".$data["obrazek"]);//vymazani souboru
      }
      session_register('hlaseniOK');
      $_SESSION['hlaseniOK'] = $texty['kartaOdebratOK'];
      session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
      header('Location: '.$soubory['upravitKarta']);
      exit;
    }
  }
  else { //nastala chyba
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['neexistujiciKarta'];
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
  }
}


/*******************************************************************************
 * zpracovani promennych predanych v POST
 ******************************************************************************/
if (!session_is_registered('promenneFormulare')) { // musí existovat registrace kontextu promìnných formuláøe
  session_register('promenneFormulare');
}
foreach($_POST as $jmenoPromenne => $hodnota) { // promìnné formuláøe jsou pøedávány pøes POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
} // foreach

$nazev = $_SESSION['promenneFormulare']["nazev_input"];
$cv = $_SESSION['promenneFormulare']["cv_input"]; //cislo vykresu
$jednotka = $_SESSION['promenneFormulare']["jednotka"];
$limit = $_SESSION['promenneFormulare']["limit"];
$cenaPrace = $_SESSION['promenneFormulare']["cenaPrace"];
$id = odstraneniEscape($_POST["id"] ?? '', 100);

//kontroly zadanych dat
$korektniParametry = true;
// nazev
if (empty($nazev)) { // nazev nesmi byt prazdny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdnyNazev'];
  $korektniParametry = false;
}
if (strlen($nazev) > 30) { // nazev nesmi byt delsi nez 30 znakù
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['nazevNad30'];
  $korektniParametry = false;
}
if (strpos($nazev, "+") != false) { // nazev nesmi obsahovat +
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['nazevMaPlus'];
  $korektniParametry = false;
}
// cislo vykresu
if (empty($cv)) { // cislo vykresu nesmi byt prazdne
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdneCV'];
  $korektniParametry = false;
}
if (strlen($cv) > 30) { // cislo vykresu nesmi byt delsi nez 30 znakù
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['cvNad30'];
  $korektniParametry = false;
}
// limit
if ($limit == "") { // limit nesmi byt prazdny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdnyLimit'];
  $korektniParametry = false;
}
if ((!preg_match("/[0-9]/", $limit)) || // limit muze obsahovat pouze cislice
    ($limit < 0)) { //a nesmi byt zaporny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['spatnyLimit'];
  $korektniParametry = false;
}
// cena prace
if ($cenaPrace == "") { // cena prace nesmi byt prazdna
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdnaCenaPrace'];
  $korektniParametry = false;
}
if ((!preg_match("/[0-9]/", $cenaPrace)) || // cena prace muze obsahovat pouze cislice
    ($cenaPrace < 0)) { //a nesmi byt zaporna
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['spatnaCenaPrace'];
  $korektniParametry = false;
}
// vsechny prodejni ceny
$dotaz = "SELECT id as id_kat FROM prodejni_kategorie ORDER BY popis ASC";
$vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
While ($data = @mysqli_fetch_array($vysledek)) {
  $cena = $_SESSION['promenneFormulare']["prodejniCena".$data["id_kat"]] ?? "";
  if ($cena != "") { // prodejni cena nesmi byt prazdna
    if ((!preg_match("/[0-9]/", $cena)) || //prodejni cena muze obsahovat pouze cislice
        ($cena < 0)) { //a nesmi byt zaporna
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['spatnaProdejniCena'];
      $korektniParametry = false;
    }
  }
}//while

if (! $korektniParametry)  { // byly chyby
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}


/*******************************************************************************
 * vkladani NOVE karty
 ******************************************************************************/
if($_POST["odeslat"] == $texty["pridatKartu"]) {
  $zacatekCV = dejZacVykresu($cv); //pouze prvni cast c. vykresu
  $cenaPrace = str_replace(",", ".", $cenaPrace); //pripadne desetinne carky nahradi za tecky
  //ulozeni hlavicky karty
  $dotaz = "INSERT INTO zbozi (id, nazev, c_vykresu, zac_c_vykresu, jednotka, min_limit, cena_prace, mnozstvi) VALUES (0, '$nazev', '$cv', '$zacatekCV', '$jednotka', '$limit', '$cenaPrace', 0)";
  mysqli_query($SRBD, $dotaz);
  if (mysqli_errno($SRBD) != 0) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novaKartaDuplicitni'];
    header('Location: '.$soubory['novaKarta']);
    exit;
  }
  else {//hlavicka karty je v poradku
    //zjisteni id prave vlozene karty
    $dotaz = "SELECT id FROM zbozi WHERE nazev='$nazev' AND c_vykresu='$cv'";
    $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
    While ($data = @mysqli_fetch_array($vysledek)) {
      $id = $data["id"];
    }
    //ulozeni prodejnich cen
    $dotaz = "SELECT id as id_kat, popis FROM prodejni_kategorie ORDER BY popis ASC";
    $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
    While ($data = @mysqli_fetch_array($vysledek)) {
      $idKat = $data["id_kat"];
      $cena = $_SESSION['promenneFormulare']["cenaPrace".$idKat];
      
      if($cena == "") { //misto ceny se vlozi NULL
        $dotaz = "INSERT INTO prodejni_ceny (id, id_zbozi, id_kategorie, cena) VALUES (0, '$id', '$idKat', 'NULL')";
        mysqli_query($SRBD, $dotaz);
      }
      else {
        $cena = str_replace(",", ".", $cena);//pripadne desetinne carky nahradi za tecky
        $dotaz = "INSERT INTO prodejni_ceny (id, id_zbozi, id_kategorie, cena) VALUES (0, '$id', '$idKat', '$cena')";
        mysqli_query($SRBD, $dotaz);
      }
    }

    //ulozeni obrazku
    if (is_uploaded_file($_FILES["jmeno_souboru"]["tmp_name"])) {
      $name = $_FILES["jmeno_souboru"]["name"];
      move_uploaded_file($_FILES["jmeno_souboru"]["tmp_name"], "./nahledy/$name");
      $dotaz = "UPDATE zbozi SET obrazek='$name' WHERE id='$id'";
      mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
      zmensiObrazek($name, "thumb");
      zmensiObrazek($name, "normal");
    }
/*
    else {
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['novyObrazekChyba'];
      header('Location: '.$soubory['novaKarta'].'?id='.$id);
      exit;
    }
*/
    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['novaKartaOK'];
    session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
    header('Location: '.$soubory['novaKarta'].'?id='.$id);
    exit;
  }

} //if pridatKartu

/*******************************************************************************
 * uprava STAVAJICI karty
 ******************************************************************************/
if($_POST["odeslat"] == $texty["ulozitZmeny"]) {
  $zacatekCV = dejZacVykresu($cv); //pouze prvni cast c. vykresu
  $cenaPrace = str_replace(",", ".", $cenaPrace); //pripadne desetinne carky nahradi za tecky
  $dotaz = "UPDATE zbozi SET nazev='$nazev', c_vykresu='$cv', zac_c_vykresu='$zacatekCV', jednotka='$jednotka', min_limit='$limit', cena_prace='$cenaPrace' WHERE id='$id'";
  mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());

  if (mysqli_errno() != 0) { //vkladan duplicitni zaznam
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novaKartaDuplicitni'];
    header('Location: '.$soubory['upravitKarta'].'?id='.$id);
    exit;
  }
  else {
    $dotaz = "SELECT id as id_kategorie FROM prodejni_kategorie ORDER BY id";
    $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
    While ($data = @mysqli_fetch_array($vysledek)) {
      $cena = $_SESSION['promenneFormulare']['prodejniCena'.$data['id_kategorie']];

      $idKat = $data["id_kategorie"];
      //pokusi se ulozit prodejni cena, pokud uz vyrobek u cenove kategorie ma
      //cenu ulozenou, tato hodnota se updatuje
      if($cena == "") { //misto ceny se vlozi NULL
        $dotaz = "INSERT INTO prodejni_ceny (id, id_zbozi, id_kategorie, cena) VALUES (0, '$id', '$idKat', 'NULL')";
        mysqli_query($SRBD, $dotaz);
      }
      else {
        $cena = str_replace(",", ".", $cena);//pripadne desetinne carky nahradi za tecky
        $dotaz = "INSERT INTO prodejni_ceny (id, id_zbozi, id_kategorie, cena) VALUES (0, '$id', '$idKat', '$cena')";
        mysqli_query($SRBD, $dotaz);
      }
      
      if (mysqli_errno() != 0) { //vkladan duplicitni zaznam
        if($cena == "") { //misto ceny se vlozi NULL
          $dotaz = "UPDATE prodejni_ceny SET cena=NULL WHERE id_zbozi='$id' AND id_kategorie='$idKat'";
          mysqli_query($SRBD, $dotaz);
        }
        else {
          $cena = str_replace(",", ".", $cena);//pripadne desetinne carky nahradi za tecky
          $dotaz = "UPDATE prodejni_ceny SET cena='$cena' WHERE id_zbozi='$id' AND id_kategorie='$idKat'";
          mysqli_query($SRBD, $dotaz);
        }
      }
    }//while

    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['editOK'];
    session_unregister('promenneFormulare');  // zru¹ení kontextu formuláøe
    header('Location: '.$soubory['upravitKarta'].'?id='.$id);
    exit;
  }
} // if $texty["ulozitZmeny"]


?>
