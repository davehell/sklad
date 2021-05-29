<?php
/**
 * vlozTransakci.php
 *
 */  

header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
include "iTransakceFunkce.php";

session_start();

//$nedostatek = array();
//$reserved = array();
    
if(!isset($_GET['id']) || !isset($_GET['skupina']) || !ereg($soubory['dokladTransakce'],$_SERVER['HTTP_REFERER']))
{
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['ChybaMazaniTransakce'];
      header("Location: ".$soubory['hlavniStranka']);
      exit;
}

$skupina = $_GET['skupina'];
$id = $_GET['id'];



akceProSkupinu($skupina,$id);

/**
 * Vykona pro prislusnou skupinu patricnou akci (kontroly, prepocty,vlozeni)
 * @param skupina   Nákup, Prodej, ...
 * @param id        id zbozi v transakci
 */  
function akceProSkupinu($skupina,$id)
{
   global $texty;
   
   //zjisteni mnozstvi
   if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
      $SRBD=spojeniSRBD();
   }
   $dotaz = "SELECT * FROM transakce WHERE id='$id'";
   $vysledek = mysqli_Query($dotaz,$SRBD);
   $data = mysqli_Fetch_Array($vysledek);
   $mnozstvi = $data['mnozstvi'];  
   $id_zbozi = $data['id_zbozi'];
   
   //echo $id_zbozi;
////////////////////////////////////////////////////////////////////////////////   
//////////////////       AKCE PRED MAZANIM           ///////////////////////////
////////////////////////////////////////////////////////////////////////////////
//    if($skupina=="Nákup")
//    { //vypocet nove prumerne ceny
//      //countAndSetPrumerna($id_zbozi);
//      zmenaMnozstviZbozi('odeber', $id_zbozi, $mnozstvi);
//    }
//    elseif($skupina=="Prodej" || $skupina=="Rezervace")
//    { 
//      zmenaMnozstviZbozi('pridej', $id_zbozi, $mnozstvi);
//    }
//    elseif($skupina=="Výroba" )
//    {
//       //odeberou se soucastky ze kterych se vyrabi vyrobek
//       akceSoucastkyCelku('pridej',$id_zbozi,$mnozstvi,$id);
//       zmenaMnozstviZbozi('odeber', $id_zbozi, $mnozstvi);
//    }
//    elseif($skupina=="Zmetkování")
//    {
//       zmenaMnozstviZbozi('pridej', $id_zbozi, $mnozstvi);
//    }
//    elseif($skupina=="Kooperace")
//    {
//       akceSoucastkyCelku('pridej',$id_zbozi,$mnozstvi);
//       pridejDoSkladu($id,$mnozstvi);
//    }   
//    elseif($skupina=="Inventura")
//    {
//      
//      zmenaMnozstviZbozi('odeber', $id_zbozi, $mnozstvi);
//    }

   ///////////////////////////////////////////////////////////////////////////////
   //////////////////////      MAZANI        ///////////////////////////////////
   ///////////////////////////////////////////////////////////////////////////////

   if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
      $SRBD=spojeniSRBD();
   }
   $dotaz = "DELETE FROM transakce WHERE id='$id'";
   $vysledek = mysqli_Query($dotaz,$SRBD);

   if (mysqli_errno() != 0) { //vkladan duplicitni zaznam
     //echo 'AA';
     session_register('hlaseniChyba');
     $_SESSION['hlaseniChyba'] = $texty['ChybaMazaniTransakce'];
   }
   else
   {
     session_register('hlaseniCK');
     $_SESSION['hlaseniOK'] = $texty['MazaniTransakceOK'];
   }
   /////////////////////////////////////////////////////////////////////////////
   ////////////////      AKCE PO MAZANI             ////////////////////////////
   /////////////////////////////////////////////////////////////////////////////
//    if($skupina=='Výroba' || $skupina=='Kooperace' || $skupina=='Nákup' || $skupina=='Inventura')
//    {  countAndSetPrumerna($id_zbozi);
// 
//    }
   header("Location: ".$_SERVER['HTTP_REFERER']);
   exit;
   
}
    


  
?>
