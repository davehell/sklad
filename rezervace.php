<?php
/**
 * pridat_zapis.php
 *
 */
header("Content-Type: text/html; charset=iso-8859-2");
include "iSkladObecne.php";
session_start();

if (!session_is_registered('promenneFormulare')) { // musí existovat registrace kontextu promìnných formuláøe
  session_register('promenneFormulare');
}

$datum = $_POST["datum"];
$cDokladu = $_POST["cDokladu"];
$id = $_POST["id"];
     
if(isset($_POST['rezNaProdej']))
{
  $korektniParametry = checkFormData();
  //
  if(!$korektniParametry) //chyba ve formulari
  {
    header('Location: '.$soubory['dokladTransakce'].'?id='.$id);
    exit;
  }
  else //vse OK ve formulari
  {
    if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
      $SRBD=spojeniSRBD();
    }
    
    $timestamp_date = strtotime($datum);
    $dotaz = "UPDATE doklady SET c_dokladu=$cDokladu, datum='$timestamp_date', skupina='Prodej' WHERE id='$id'";
    mysqli_query($SRBD, $dotaz);
    
    if (mysqli_errno($SRBD) != 0) { //vkladan duplicitni zaznam
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['NovyDokladDuplicitni'];
      
    }
    else {
      session_register('hlaseniOK');
      $_SESSION['hlaseniOK'] = $texty['rezervacePrevedena'];
      }
  }
}
elseif(isset($_POST['ruseniRez']))
{
 if (!isset($SRBD)) { // u¾ jsme pøipojeni k databázi
      $SRBD=spojeniSRBD();
    }
    //nejprve smazu vsechny polozky ktere jsou na rezervaci
    $dotaz = "SELECT T.id as id, T.id_zbozi, T.mnozstvi
              FROM transakce as T JOIN doklady as D ON T.id_dokladu=D.id 
              WHERE D.id='$id'";
    $vysledek = mysqli_query($SRBD, $dotaz);
    if(mysqli_num_rows($vysledek) > 0)         //jsou polozky v dokladu
    {
      While ($data = @mysqli_fetch_array($vysledek)) {
        $id_transakce = $data['id'];
        // pro kazdou transakci musim vratit zbozi do skladu
        
        //nakonec ho smazu z db
        $dotaz2 = "DELETE FROM transakce WHERE id=$id_transakce";
        mysqli_query($SRBD, $dotaz2);
      }
    }
    //smazani polozky z tabulky doklady
    $dotaz = "DELETE FROM doklady WHERE id=$id";
    mysqli_query($SRBD, $dotaz);
    if (mysqli_errno($SRBD) != 0) { //vkladan duplicitni zaznam
      session_register('hlaseniChyba');
      $_SESSION['hlaseniChyba'] = $texty['chybaMazaniRezervace'];
      
    }
    else {
      session_register('hlaseniOK');
      $_SESSION['hlaseniOK'] = $texty['rezervaceSmazana'];
      header('Location: '.$soubory['hlavniStranka']);
      exit;
     }
   

} //konec
header('Location: '.$soubory['dokladTransakce'].'?id='.$id);
exit;
   

/**
 * kontrola validity formularovych dat
 * @return TRUE pokud je vse v poradku jinak FALSE a nastavenu SESSION s chybou 
 */ 
function checkFormData()
{
global $texty;
global $datum;
$korektniParametry = true;

//datum
if (empty($_POST["datum"])) { // nazev nesmi byt prazdny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdneDatum'];
  $korektniParametry = false;
}
else if (!ereg("^([0-9][0-9]?).([0-9][0-9]?).([0-9]{4})$",$_POST["datum"], $dateParts))
{  session_register('hlaseniChyba');
   $_SESSION['hlaseniChyba'] = $texty['nespravneDatum'];
   $korektniParametry = false;
}
else
{
   $datum = $dateParts[3].'-'.$dateParts[2].'-'.$dateParts[1];
}
//cDokladu
if(empty($_POST["cDokladu"])) { // nazev nesmi byt prazdny
  session_register('hlaseniChyba');
  $_SESSION['hlaseniChyba'] = $texty['prazdneCDokladu'];
  $korektniParametry = false;
}




return $korektniParametry;
}

?>
