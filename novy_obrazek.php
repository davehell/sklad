<?php
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
session_start();

if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
  $SRBD=spojeniSRBD();
}


//odebrani obrazku. id zbozi je v parametru "odstranit"
if(isset($_GET["odstranit"])) {
  $id = $_GET["odstranit"];
  $dotaz = "SELECT obrazek FROM zbozi WHERE id='$id'";
  $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
  $data = mysqli_fetch_array($vysledek);
  if(File_Exists("nahledy/".$data["obrazek"])) unlink("nahledy/".$data["obrazek"]);  //vymazani souboru
  if(File_Exists("nahledy/thumb_".$data["obrazek"])) unlink("nahledy/thumb_".$data["obrazek"]);//vymazani souboru
  $dotaz = "UPDATE zbozi SET obrazek=NULL WHERE id='$id'";
  mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());

  session_register('hlaseniOK');
  $_SESSION['hlaseniOK'] = $texty['odebratObrazekOK'];
  header('Location: '.$soubory['upravitKarta'].'?id='.$id);
  exit;
}

//nahrani obrazku na server
if(isset($_POST["id"])) {
  $id = $_POST["id"];
  if (is_uploaded_file($_FILES["jmeno_souboru"]["tmp_name"])) {
    //nahrani noveho obrazku
    $name = $_FILES["jmeno_souboru"]["name"];
    move_uploaded_file($_FILES["jmeno_souboru"]["tmp_name"], "./nahledy/$name");
    zmensiObrazek($name, "thumb");
    zmensiObrazek($name, "normal");
    //smazani predchoziho obrazku
    $dotaz = "SELECT obrazek FROM zbozi WHERE id='$id'";
    $vysledek = mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());
    $data = mysqli_fetch_array($vysledek);
    if(File_Exists("nahledy/".$data["obrazek"])) unlink("nahledy/".$data["obrazek"]);  //vymazani souboru
    if(File_Exists("nahledy/thumb_".$data["obrazek"])) unlink("nahledy/thumb_".$data["obrazek"]);//vymazani souboru
    //ulozeni nazvu noveho souboru do db
    $dotaz = "UPDATE zbozi SET obrazek='$name' WHERE id='$id'";
    mysqli_query($SRBD, $dotaz) or Die(mysqli_Error());

    session_register('hlaseniOK');
    $_SESSION['hlaseniOK'] = $texty['novyObrazekOK'];
    header('Location: '.$soubory['upravitKarta'].'?id='.$id);
    exit;
  }
  else {
    session_register('hlaseniChyba');
    $_SESSION['hlaseniChyba'] = $texty['novyObrazekChyba'];
    header('Location: '.$soubory['upravitKarta'].'?id='.$id);
    exit;
  }
}
?>
