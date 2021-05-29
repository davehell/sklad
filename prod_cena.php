<?php

  /**
   * nazev_cv.php
   * dotaze se na cv daneho nazvu a vrati v xml
   */        
  include 'iSkladObecne.php';
  session_start();
  
  header('Content-Type: text/xml; charset=iso-8859-2');
  if (isset($_REQUEST['n']) && isset($_REQUEST['kat']) && isset($_REQUEST['cv'])){ 
    $nazev = $_REQUEST['n'];
    $cv = $_REQUEST['cv'];
    $kategorie = $_REQUEST['kat'];
  }
  else {  
    $cv = "";
    $nazev = "";
    $kategorie = "";
  }
  $newnazev=iconv('utf-8','iso-8859-2',$nazev);
  
  //put result in XML structure
  $dom = new DOMDocument();
  
  $SRBD=spojeniSRBD();
  $vysledek = mysqli_Query("SELECT cena FROM prodejni_ceny, zbozi 
                           WHERE zbozi.id=prodejni_ceny.id_zbozi 
                             AND nazev = '$newnazev' 
                             AND c_vykresu = '$cv'
                             AND id_kategorie=$kategorie", $SRBD);
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
  // detekce chyby
  if(mysqli_num_rows($vysledek)==0)
  { $cv = $dom->CreateElement('cena');
    $dom->appendChild($cv);
    $cvText = $dom->createTextNode("chyba");
    $cv->appendChild($cvText);
  }
  While ($data = mysqli_Fetch_Array($vysledek)) {
    $cv = $dom->CreateElement('cena');
    $dom->appendChild($cv);
    $cvText = $dom->createTextNode($data['cena']);
    $cv->appendChild($cvText);
  }
  $xmlString = $dom->saveXML();
  print( $xmlString);
  
?>
