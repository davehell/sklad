<?php

  /**
   * nazev_cv.php
   * dotaze se na cv daneho nazvu a vrati v xml
   */        
  include 'iSkladObecne.php';
  include 'iTransakceFunkce.php';
  session_start();
  
  header('Content-Type: text/xml; charset=iso-8859-2');
  if (isset($_REQUEST['n']) && (isset($_REQUEST['cv'])) && (isset($_REQUEST['typ'])))
  { $nazev = $_REQUEST['n'];
    $cv = $_REQUEST['cv'];
    $typ = $_REQUEST['typ'];
  }
  else 
    $nazev = "";

  $newnazev=iconv('utf-8','iso-8859-2',$nazev);
  
  //zjisteni id
  $SRBD=spojeniSRBD();
  $dotaz = "SELECT id FROM zbozi WHERE nazev = '$newnazev' AND c_vykresu='$cv'";
  $vysledek = MySQL_Query($dotaz, $SRBD);
  $data = MySQL_Fetch_Array($vysledek);
  $id = $data['id'];

  if($typ=='nakup')
  {  $cena = getPrumernaCena($id);
     if($cena=='') $cena = '-';
       $cenap = getPosledniCena($id, 'MJ');
     if($cenap=='') 
       $cena .= ';-';
     else
       $cena .= ';'.getPosledniCena($id, 'MJ');
  
  }
  elseif($typ=='koop')
  { 
    $cena = getPosledniCena($id, 'KOO');
    // pokud nic neni vlozime pomlcku
    if($cena == '')
      $cena = '-';
  }
  //$handle = fopen("aaaa.txt", "w");
  //fwrite($handle, $id.'xxx'.$cena.'xxx'.$dotaz);
  
  //put result in XML structure
  $dom = new DOMDocument();
  $cenovka = $dom->CreateElement('cena');
  $dom->appendChild($cenovka);
  $cenText = $dom->createTextNode($cena);
  $cenovka->appendChild($cenText);
  $attr = $dom->createAttribute('typ');
  $att_text = $dom->createTextNode($typ);
  $attr->appendChild($att_text);
  $cenovka->appendChild($attr);
 
  $xmlString = $dom->saveXML();
  print( $xmlString);
  
?>
