<?php

//
//   Soubory pou¾ité v aplikaci
//

// katalog jmen souborù informaèního systému

$soubory = array (
  'includePrihlaseni'     =>      'iSkladPrihlaseni.php',            // prihlaseni uzivatele
   'hlavniStranka'        =>      'index.php',                       // hlavní stránka s mo¾ností výpisù kódu programù
   'includeObecne'        =>      'iSkladObecne.php',               // spoleèné funkce
   'includeSoubory'       =>      'iSkladSoubory.php',              // jména pou¾itých souborù - tento soubor
   'includeTexty'         =>      'iSkladTexty.php',                // texty pou¾ité v systému
   'includeKonstanty'     =>      'iSkladKonstanty.php',            // konstanty pou¾ité v systému

   'prihlaseni'           =>      'prihlaseni.php',            // prihlaseni
   'odhlaseni'            =>      'odhlaseni.php',            // odhlaseni
   'upravitProfil'        =>      'uprava_profilu.php',

//moduly
  'moduly'                  =>      'moduly.php',
//uzivatele
  'uzivatele'               =>      'uzivatele.php',            // sprava uzivatelu
  'novyUzivatel'            =>      'novy_uzivatel.php',
  'odebraniUzivatele'       =>      'odebrat_uzivatele.php',
  'editaceUzivatele'        =>      'editace_uzivatele.php',

//skladove karty
  'novaKarta'              =>      'karta_nova.php',
  'upravitKarta'           =>      'karta_upravit.php',
  'nahledKarta'            =>      'karta_nahled.php',
  'frmPridatKarta'         =>      'pridat_upravit_karta.php',
  'frmPridatSoucastka'     =>      'pridat_odebrat_soucastka.php',
  'frmNovyObrazek'         =>      'novy_obrazek.php',
  'prodejniCeny'           =>      'prodejni_ceny.php',
  'stroje'                 =>      'stroje.php',
  'potrebnyMaterial'       =>      'potrebny_material.php',

//zapis
  'novyDoklad'             =>      'novyDoklad.php',
  'editDoklad'             =>      'editDoklad.php',
  'vlozDoklad'             =>      'vlozDoklad.php',
  'dokladTransakce'        =>      'dokladTransakce.php',
  'dodaciList'             =>      'dodaciList.php',
  'dodaciListTisk'         =>      'dodaciListTisk.php',
  'vlozTransakci'          =>      'vlozTransakci.php',
  'iTransakceFunkce'       =>      'iTransakceFunkce.php',
  'testVyroba'             =>      'testVyroba.php',
  'testVyrobaVypis'        =>      'testVyrobaVypis.php',
  'rezervace'              =>      'rezervace.php',
  'smazTransakci'          =>      'smazTransakci.php',
  'odebratDoklad'          =>      'odebratDoklad.php',
  
//podlimitni polozky
  'podlimitniPolozky'      =>      'podlimitni_polozky.php',


//tiskove sestavy
  'podlimitniPolozky'      =>      'podlimitni_polozky.php',
  'tiskNakupy'             =>      'tiskTransakce.php?t=n',
  'tiskVyroba'             =>      'tiskTransakce.php?t=v',
  'tiskProdej'             =>      'tiskTransakce.php?t=p',
  'tiskRezervace'          =>      'tiskTransakce.php?t=r',
  'cenovyStavSkladu'       =>      'cenovyStav.php',
  'mnozstevniStavSkladu'   =>      'mnozstevniStav.php',
  'koeficient'             =>      'koeficient.php',

//archiv
  'archiv'                 =>      'archiv.php',
  'inventura'              =>      'inventura.php',
  

  
//obrazky
    'userAdd'           =>      'user_add.gif',
    'userEdit'          =>      'user_edit.gif',
    'userDelete'        =>      'user_delete.gif',
    
    
    'kontrolaMnozstvi'  =>      'kontrolaMnozstvi.php',
    

   'sentinel'=>''
);
?>
