<?php

  /**
   * nazev_cv.php
   * dotaze se na cv daneho nazvu a vrati v xml
   */        
  include 'iSkladObecne.php';
  session_start();
  
  if (isset($_REQUEST['nazev']) && !empty($_REQUEST['nazev']))
    {
      $n = $_REQUEST["nazev"];
      $new=iconv('utf-8','iso-8859-2',$n);
      $where = "where nazev = '$new'";
    }
  elseif (isset($_REQUEST['cv']) && !empty($_REQUEST['cv']))
  { $n = urldecode($_REQUEST["cv"]);
    $new=iconv('utf-8','iso-8859-2',$n);
    $where = "where c_vykresu = '$new'";
  }
  else
    $where = '';

  $dotaz = "SELECT id, nazev, c_vykresu FROM zbozi $where";
  //put result in XML structure
  
  $result = "<?xml version=\"1.0\" encoding=\"iso-8859-2\"?>\n";
  $result .= "<prvky>\n";
  $SRBD=spojeniSRBD();

 $vysledek = MySQL_Query($dotaz, $SRBD);
  //testovani zaregistrovane session, aby se mohla vybrat jako hodnota v nabidce
   While ($data = MySQL_Fetch_Array($vysledek)) {
    $result.= "\t<prvek>";
    if (isset($_REQUEST['nazev']))  
      $result.= $data['c_vykresu'];
    elseif (isset($_REQUEST['cv']))
      $result.= $data['nazev'];
    $result.="</prvek>\n";
     }

  $result .= "</prvky>";


header('Content-Type: text/xml; charset=iso-8859-2'); 
echo $result;

?>
