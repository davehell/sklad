// obsluha formulare pro vyber nazvu a c.vykresu
function createXmlHttpRequestObject(){
  //creates XMLHttpRequest object if it is possible
  var xmlhttp
  try {
  //should work on all browsers except IE6 or older
    xmlHttp = new XMLHttpRequest();
  }
  catch (e){
    //browser is IE6 or older
    try {
      xmlHttp = new ActiveXObject("Microsoft.XMLHttp");
    }
    catch (e){
      //ignore error
    }
  }
  if (!xmlHttp)
    alert ("Error creating the XMLHttpRequest object.");
  else 
    return xmlHttp;
}
/**
 * odeslani pozadavku pro PHP skript
 */ 
function vyber_cenu()
{
  
  request = createXmlHttpRequestObject();
  if(request)
  {
      kategorie = document.getElementById("prod_kat").value;
      
      nazev = document.getElementById("nazev").value;
      cv = document.getElementById("cv").value;
      //alert (kategorie);
      if(cv && cv!='')
      { 
        var params = "n="+nazev+"&kat="+kategorie+"&cv="+cv;
        xmlHttp.open("POST", "prod_cena.php", true);
        xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-2");
        xmlHttp.onreadystatechange = zpracovaniProdCena;
        xmlHttp.send(params);
      }
  }
}

/**
 * zpracovani XML odpovedi
 */ 
function zpracovaniProdCena(){
  if (xmlHttp.readyState == 4)
  {
    if(xmlHttp.status == 200)
    {
    //http status is OK
      try {
        var xmlResponse = xmlHttp.responseXML;
        var xmlRoot = xmlResponse.documentElement;
        var responseText = xmlRoot.firstChild.data;
        submit = document.getElementById("odeslat");
        //alert ("AAA");
        submit = document.getElementById("odeslat");
        prodcena = document.getElementById("prod_cena");
        //alert ("AAA");
        if(prodcena && submit)
        {  prodcena.innerHTML = "";
           if(responseText!="chyba")
           { 
             prodcena.innerHTML += responseText;
             cenaMJ=document.getElementById("cenaMJ");
             cenaMJ.value=responseText;
             submit.disabled = false; 
           }
           else
             {
               prodcena.innerHTML += "--není cena--";
               submit.disabled = true;
             }
        }
      }
      catch (e){
        alert("Error reading the response: " + e.toString());
      }
    }
    else
    {
        alert("Chyba pri nacitani stanky"+ httpRequest.status +":"+ httpRequest.statusText);
    }
  }
  osetriRadio();
}

/**
 * odeslani pozadavku pro PHP skript
 */ 
function dej_cenu(typ)
{ 
  request = createXmlHttpRequestObject();
  if(request)
  {
      nazev = document.getElementById("nazev").value;
      cv = document.getElementById("cv").value;
      
      if(cv && cv!='')
      { 
        var params = "n="+nazev+"&typ="+typ+"&cv="+cv;
        //alert(params);
        xmlHttp.open("POST", "dej_cenu.php", true);
        xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-2");
        xmlHttp.onreadystatechange = zpracovaniCena2;
        xmlHttp.send(params);
      }
  }
}

/**
 *
 */
function zpracovaniCena2(){
  if (xmlHttp.readyState == 4)
  {
    if(xmlHttp.status == 200)
    {
    //http status is OK
      try {
        var xmlResponse = xmlHttp.responseXML;
        var xmlRoot = xmlResponse.documentElement;
        
        var responseText = xmlRoot.firstChild.data;
        var typ_ceny = xmlRoot.getAttribute('typ');
        submit = document.getElementById("odeslat");
        //alert ("AAA");
        //typ_ceny = 'prod';
        if(typ_ceny == 'nakup')
        {
          
          var re1=/[;]+/;
          var result=responseText.split(re1);
          prum_cena = result[0];
          posl_cena = result[1];
          button = document.getElementById("radioprum");
          if(prum_cena=='-')
          {
             prum_cena='--není--';
             if(button)
               button.disabled=true;
          }
          else
          {
             if(button)
               button.disabled=false;
          }
          cena = document.getElementById('prumn');
          if(cena)
          {
            cena.innerHTML=prum_cena;
          }
          //posledni cena
          button = document.getElementById("radioposl");
          if(posl_cena=='-')
          {
             posl_cena='--není--';
                if(button)
               button.disabled=true;
          }
          else
          {
              if(button)
               button.disabled=false;
          
          }
          cena = document.getElementById('posln');
          if(cena)
          {
            cena.innerHTML=posl_cena;
          }
          //alert(prum_cena+"---"+posl_cena);
        }
        else
        {
          posl_cena = responseText;
          button = document.getElementById("radioposl");
          if(posl_cena=='-')
          {
             posl_cena='--není--';
                if(button)
               button.disabled=true;
          }
          else
          {
              if(button)
               button.disabled=false;
          }
          cena = document.getElementById('poslk');
          if(cena)
          {
            cena.innerHTML=posl_cena;
          }
        }
      }
      catch (e){
        alert("Error reading the response: " + e.toString());
      }
    }
    else
    {
        alert("Chyba pri nacitani stanky"+ httpRequest.status +":"+ httpRequest.statusText);
    }
  }
}
 

function osetriRadio()
{
  chosen = ""
  len = document.form.prodSkupina.length
  
  for (i = 0; i <len; i++) {
  if (document.form.prodSkupina[i].checked) {
  chosen = document.form.prodSkupina[i].value
  }
  }
  
  if(chosen=="skupina")
  {
    prodcena = document.getElementById("prod_cena");
    submit = document.getElementById("odeslat");
    if(submit)
    {
      if(prodcena.innerHTML == "--není cena--")
        submit.disabled = true;
      else 
        submit.disabled = false;
    }
  }
  else //vlastni
  {
     submit = document.getElementById("odeslat");
      if (submit)
      { 
        submit.disabled = false;
      }
  }
}


document.addEventListener('DOMContentLoaded', function() {
  var hiddenClass = "hidden";
  var btn = document.getElementById("btnProdejniCeny");
  var list = document.getElementById("listProdejniCeny");

  if(btn && list) {
    list.addClass(hiddenClass);

    btn.addEventListener("click", function () {
      if(list.className.indexOf(hiddenClass) == -1) {
        list.addClass(hiddenClass);
      }
      else {
        list.removeClass(hiddenClass);
      }
    });
  }
});
