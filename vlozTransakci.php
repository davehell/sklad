<?php
/**
 * vlozTransakci.php
 *
 */  

header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
include "iTransakceFunkce.php";

session_start();

$nedostatek = array();
$reserved = array();


if (!session_is_registered('promenneFormulare')) { // musí existovat registrace kontextu promìnných formuláøe
  session_register('promenneFormulare');
}
foreach($_POST as $jmenoPromenne => $hodnota) { // promìnné formuláøe jsou pøedávány pøes POST
  $_SESSION['promenneFormulare'][$jmenoPromenne] = trim(odstraneniEscape($hodnota, 100));
  //echo $jmenoPromenne."<br />";
} // foreach


$nazev = $_SESSION['promenneFormulare']["nazev"];
$cv = $_SESSION['promenneFormulare']["cv"];
$skupina = $_SESSION['promenneFormulare']['skupina'];

$korektniParametry = checkFormData($skupina);

if(!$korektniParametry) //chyba ve formulari
{
  header("Location: ".$_SERVER['HTTP_REFERER']);
}
else //vse OK ve formulari
{ 
    if (!isset($SRBD)) { // uµ jsme pøipojeni k databázi
      $SRBD=spojeniSRBD();
    }

    // kontrola existence c_vykresu a nazvu
    $vysledek = MySQL_Query("SELECT id FROM zbozi
     WHERE nazev='$nazev' AND c_vykresu='$cv'", $SRBD);
    if(mysql_num_rows($vysledek) != 1) {   //chyba
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['neexistujiciKarta'];
      header("Location: ".$_SERVER['HTTP_REFERER']);
      exit;
    }
    //vseOK
    else {
      While ($data = @MySQL_Fetch_Array($vysledek)) {
        $id = $data["id"];
      }
      //echo 'id:'.$id;
      akceProSkupinu($skupina,$id);
      //header('Location: '.$soubory['upravitKarta'].'?id='.$id);
      exit; 
    }      
    header("Location: ".$_SERVER['HTTP_REFERER']);
}


/**
 * Vykona pro prislusnou skupinu patricnou akci (kontroly, prepocty,vlozeni)
 * @param skupina   Nákup, Prodej, ...
 * @param id        id zbozi v transakci
 */  
function akceProSkupinu($skupina,$id)
{
   global $texty;
   global $nedostatek;
   global $reserved;
   
   $cenaMJ = 'NULL';     //inicializace hodnot cen (implicitne nenastaveny)
   $cenaKOO = 'NULL';
   $id_dokladu = $_SESSION["promenneFormulare"]["id_dokladu"];
   $mnozstvi = $_SESSION["promenneFormulare"]["mnozstvi"];
   $mnozstvi = str_replace(",", ".", $mnozstvi);
   
   ///////////////////////////////////////////////////////////////////////////////
   ////////////////////     AKCE PRED VKLADANIM         //////////////////////////
   ///////////////////////////////////////////////////////////////////////////////
   if($skupina == "Nákup" || $skupina == 'Inventura')
   {
    if(($_SESSION['promenneFormulare']["cenaMJ"]!="vlastni"))
    { //kontrola existence nake transakce a tedy prumerne a posledni ceny
      if (!isset($SRBD)) $SRBD=spojeniSRBD();
     
      $vysledek = MySQL_Query("SELECT * FROM transakce WHERE id_zbozi='$id'",$SRBD);
      if(mysql_num_rows($vysledek) == 0)  //neni zadna
      {
        session_register('hlaseniChyba');
        $_SESSION['hlaseniChyba'] = $texty['neniPrumCena'];
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit;          
      }
      
      if($_SESSION['promenneFormulare']["cenaMJ"]=="prumerna")
      {
        $cenaMJ=getPrumernaCena($id);
      }
      elseif($_SESSION['promenneFormulare']["cenaMJ"]=="posledni")
      {
        $cenaMJ=getPosledniCena($id,'MJ');
      }
    }
    else //zadana vlastni cena => vkladame
    {
       $cenaMJ = $_SESSION['promenneFormulare']["cenaMJvlastni"];
    }
   }
   elseif($skupina == "Prodej" || $skupina=="Zmetkování" || $skupina == "Rezervace")
   {
     //zjisteni poctu kusu na sklade
     if (!isset($SRBD)) $SRBD=spojeniSRBD();
     
     //echo 'ID:'.$id;
     $vysledek = MySQL_Query("SELECT mnozstvi FROM zbozi WHERE id='$id'",$SRBD);
     $data = MySQL_Fetch_Array($vysledek);
     $skladMnozstvi = $data["mnozstvi"];
     if($skladMnozstvi < $mnozstvi)
     {  // zbozi je malo
        $vysledek2 = MySQL_Query("SELECT sum(T.mnozstvi) as rezervovano
                                  FROM transakce as T join doklady as D on T.id_dokladu=D.id 
                                  WHERE D.skupina = 'Rezervace'  AND T.id_zbozi='$id' 
                                  GROUP BY T.id_zbozi",$SRBD);
        $data2 = MySQL_Fetch_Array($vysledek2);
        if(MySQL_num_rows($vysledek2)==0)
          $rezervovano = '0';
        else 
          $rezervovano = $data2['rezervovano'];
        session_register('hlaseniChyba');
        $_SESSION['hlaseniChyba'] = $texty['maloZbozi'].': '.$skladMnozstvi .
                                    '<br /> '.$texty['pozadovano'].': '.$mnozstvi .
                                    '<br /> '.$texty['rezervovano'].': '.$rezervovano;
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit; 
     }
     //priradime hodnotu prodejni ceny
     else if($skupina=="Prodej" || $skupina=="Rezervace")
     {
            if($_SESSION['promenneFormulare']["prodSkupina"] == 'skupina')
              $cenaMJ = $_SESSION['promenneFormulare']["cenaMJ"];
            else
            {
              if(is_numeric($_SESSION['promenneFormulare']["cenaMJvlastni"]))
                $cenaMJ = $_SESSION['promenneFormulare']["cenaMJvlastni"];
              else
              {
                session_register('hlaseniChyba');
                $_SESSION['hlaseniChyba'] = $texty['chybaVlastniCena'];
                header("Location: ".$_SERVER['HTTP_REFERER']);
                exit;
              }
            }

     }
          else
             $cenaMJ=getPrumernaCena($id);   // u rezervace si ulozim aktualni prumernou cenu
    }
    elseif($skupina == "Kooperace")
    {
      $stupen_zanoreni = 1;   // stupen zanoreni 1
      if(!lzeVyrobit2($id, $mnozstvi, $stupen_zanoreni))
      {   
          session_register('hlaseniChyba');
          $_SESSION['hlaseniChyba'] = $texty['nejsouSoucastky']."\n".printNedostatekTable($nedostatek);
          header("Location: ".$_SERVER['HTTP_REFERER']);
          exit;          
      }
      else      //lze vyrobit
      {
        if(($_SESSION['promenneFormulare']["cenaKOO"]!="vlastni"))
        { //kontrola existence nake transakce a tedy prumerne a posledni ceny
          if (!isset($SRBD)) $SRBD=spojeniSRBD();
         
          $vysledek = MySQL_Query("SELECT * FROM transakce WHERE id_zbozi='$id'",$SRBD);
          if(mysql_num_rows($vysledek) == 0)  //neni zadna
          {
            session_register('hlaseniChyba');
            $_SESSION['hlaseniChyba'] = $texty['neniPrumCena'];
            header("Location: ".$_SERVER['HTTP_REFERER']);
            exit;          
          }
          elseif($_SESSION['promenneFormulare']["cenaKOO"]=="posledni")
          {
            $cenaKOO=getPosledniCena($id,'KOO');
          }
        }
        else 
          $cenaKOO = $_SESSION['promenneFormulare']["cenaKOOvlastni"];
        
        //pridani cenaMJ jako nova cena zbozi (soucet cen materialu+cena za kooperaci)
        
        $cenaMJ = sumaCenMaterialu($id);
        $cenaMJ +=  $cenaKOO;        
        //echo $cenaKOO.'AAA'.$dataCen['cena_matr'];    
      }    
    }
    elseif($skupina=="Výroba")            // výroba
    {
      $stupen_zanoreni = 1;   // stupen zanoreni 1
      if(!lzeVyrobit2($id, $mnozstvi, $stupen_zanoreni))
      {   
          session_register('hlaseniChyba');
          $_SESSION['hlaseniChyba'] = $texty['nejsouSoucastky']."\n".printNedostatekTable($nedostatek);
          header("Location: ".$_SERVER['HTTP_REFERER']);
          exit;          
      }
      else      //lze vyrobit - spocitani ceny vyrobku, tady by se melo nasobit koeficientem
      {
         $cenaMJ = sumaCenMaterialu($id);
         $koeficient = getCoefficient($SRBD);
         $cenaKOO = cenaPrace($id);
         $cenaMJ += ($cenaKOO*$koeficient);
      }
    }
    elseif($skupina=="Inventura")                 //pocatecni naplneni skladu
    {
      // nic se neprovadi
    }
    
   ///////////////////////////////////////////////////////////////////////////////   
   //////////////////////      VKLADANI        ///////////////////////////////////
   ///////////////////////////////////////////////////////////////////////////////
   
   if (!isset($SRBD)) { // uµ jsme pøipojeni k databázi
      $SRBD=spojeniSRBD();
   }
   
   $cenaMJ = str_replace(",", ".", $cenaMJ); //pripadne desetinne carky nahradi za tecky
   $cenaKOO = str_replace(",", ".", $cenaKOO); //pripadne desetinne carky nahradi za tecky
   //$dotaz = "INSERT INTO transakce(id,id_zbozi, id_dokladu, mnozstvi, cena_MJ, cena_KOO)
   //                         VALUES (0,'$id','$id_dokladu','$mnozstvi',$cenaMJ,$cenaKOO)";
   $vysledek = MySQL_Query("INSERT INTO transakce(id,id_zbozi, id_dokladu, mnozstvi, cena_MJ, cena_KOO)
                            VALUES (0,'$id','$id_dokladu','$mnozstvi',$cenaMJ,$cenaKOO)",$SRBD) or Die(MySQL_Error());
   $id_transakce = mysql_insert_id();      //zjisteni posledniho id - pro vyrobu
   
   
   if (mysql_errno() != 0) { //vkladan duplicitni zaznam
     session_register('hlaseniChyba');
     $_SESSION['hlaseniChyba'] = $texty['ChybaVlozeniTransakce'];
     header("Location: ".$_SERVER['HTTP_REFERER']);
     exit;
   }
   session_unregister("promenneFormulare");
   
   ///////////////////////////////////////////////////////////////////////////////   
   /////////////////       AKCE PO VLOZENI         ///////////////////////////////
   ///////////////////////////////////////////////////////////////////////////////
   session_register("hlaseniOK");    // bude hlaseni o uspesnem provedeni
   if($skupina=="Nákup" )
   { //vypocet nove prumerne ceny
   
//      countAndSetPrumerna($id);
//      zmenaMnozstviZbozi('pridej', $id, $mnozstvi);
     $_SESSION['hlaseniOK']=$texty['nakupOK'];
   }
   elseif($skupina=="Prodej" || $skupina=="Rezervace")
   {
//      zmenaMnozstviZbozi('odeber', $id, $mnozstvi);
     if($skupina=="Prodej")
       $_SESSION['hlaseniOK']=$texty['prodejOK'];
     elseif($skupina == "Rezervace")   
       $_SESSION['hlaseniOK']=$texty['rezervaceOK'];
   }
   elseif($skupina=="Výroba")
   {
      //countAndSetPrumerna($id);
      //odeberou se soucastky ze kterych se vyrabi vyrobek
      //akceSoucastkyCelku('odeber',$id,$mnozstvi, $id_transakce);
      //zmenaMnozstviZbozi('pridej', $id, $mnozstvi);
      $_SESSION['hlaseniOK']=$texty['vyrobaOK'];
   }
   elseif($skupina=="Zmetkování")
   {
//       countAndSetPrumerna($id);
//       zmenaMnozstviZbozi('odeber', $id, $mnozstvi);
      $_SESSION['hlaseniOK']=$texty['zmetkovaniOK'];
   }
   elseif($skupina=="Kooperace")
   {
//       countAndSetPrumerna($id);
//       akceSoucastkyCelku('odeber',$id,$mnozstvi);
//       pridejDoSkladu($id,$mnozstvi);
      $_SESSION['hlaseniOK']=$texty['kooperaceOK'];
   }   
   elseif($skupina=="Inventura")
   {
      //rozhodnout se jak se to bude presne vkladat, jestli primo jako inventura
      //nebo pricitat k aktualnimu mnozstvi
//       countAndSetPrumerna($id);
//       zmenaMnozstviZbozi('pridej', $id, $mnozstvi);
      $_SESSION['hlaseniOK']=$texty['inventuraOK'];
   }
   
   header("Location: ".$_SERVER['HTTP_REFERER']);
   
}









?>
