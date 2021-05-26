function ukazProdejKomuVyroba(selectBox)
{
  
  hodnota = selectBox.value 
  if((hodnota == "Prodej") || (hodnota == "Rezervace") ) {
    document.getElementById("prodejniCena").disabled = false;
  }
  else {
    document.getElementById("prodejniCena").disabled = true;
  }

  hodnota = selectBox.value
  if(hodnota == "Výroba") {
    document.getElementById("typVyroby").disabled = false;
  }
  else {
    document.getElementById("typVyroby").disabled = true;
  }
}
