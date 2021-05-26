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

function vyber_cv()
{
  
  request = createXmlHttpRequestObject();
  if(request)
  {
      
      nazev = document.getElementById("nazev");
      if(nazev)
      {
        if(nazev.value == '')
        {
          var params = "nazev=";
        }
        else 
          var params = "nazev="+nazev.value;
        xmlHttp.open("POST", "nazev_cv.php", true);
        xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-2");
        xmlHttp.onreadystatechange = zpracovaniPozadavku;
        xmlHttp.send(params);
      }
  }
}

cv_hotovo = false;

function zpracovaniPozadavku(){
  if (xmlHttp.readyState == 4)
  {
    if(xmlHttp.status == 200)
    {
    //http status is OK
      try {
      //alert ('AA');
        //smazu co je v selectu
        var select = document.getElementById("cv");
        if ( select.hasChildNodes() )
        {
            while ( select.childNodes.length >= 1 )
              select.removeChild(select.firstChild);       
        }
        // pruchod xml dokumentem a vkladani do selectu
        var cvs = xmlHttp.responseXML.getElementsByTagName('prvek');
        
          //tohle se pridava pouze pokud je vice cisel vykresu
          if(cvs.length>1)
          {  
             option = document.createElement("option");  //vytvoøí element typu option
             option.value = "";
             option.innerHTML = "----- vyberte -----";
             if(select)
              select.appendChild(option);
          }
        for (var i=0; i < cvs.length; i++) {
          option = document.createElement("option");  //vytvoøí element typu option
          option.value = cvs[i].firstChild.data;
          option.innerHTML = cvs[i].firstChild.data;
          if(select)
            select.appendChild(option);
        }
        
      
      prodcena = document.getElementById("prod_cena");    
      if(prodcena)
      {
         if(cvs.length==1)
           vyber_cenu();
         else prodcena.innerHTML = '';
      }
      
      nakup1 = document.getElementById("posln");
      nakup2 = document.getElementById("prumn");
      if(nakup1 && nakup2)
      {
         if(cvs.length==1)
           dej_cenu('nakup');
         else {nakup1.innerHTML = '';nakup2.innerHTML = '';}
      }
         
      kooperace = document.getElementById("poslk");
      if(kooperace)
      {
         if(cvs.length==1)
           dej_cenu('koop');
         else kooperace.innerHTML = '';
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
  cv_hotovo = true;
  
}

/**
 * osetri funkcnost po onChaange na selectu c_vykresu
 * kontroluje zda je u nazvu nastavena naka VALUE nebo je prazdna
 * 1. prazdna -> vola AJAX funcki na zjisteni nazvu 
 * 2. value vola prislusne funkce na prodejni ceny, nebo co je potreba   
 */ 
function osetri_cv()
{
  
  cv = document.getElementById("cv");   
  if(cv)
  {  
       pozadavek_cv(cv.value);
  } 
  

}//osetri_cv

function pozadavek_cv(cv)
{
   request = createXmlHttpRequestObject();
   if(request)
   {
         if(cv=='')
           var params = "cv=";
         else
           var params = "cv="+cv;
         xmlHttp.open("POST", "nazev_cv.php", true);
         xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-2");
         xmlHttp.onreadystatechange = zpracovani_cv;
         xmlHttp.send(params);
       
   }

}

function zpracovani_cv()
{
  if (xmlHttp.readyState == 4)
  {
    if(xmlHttp.status == 200)
    {
    //http status is OK
      try {
        //smazu co je v selectu
        var select = document.getElementById("nazev");
        if ( select.hasChildNodes() )
        {
            while ( select.childNodes.length >= 1 )
              select.removeChild(select.firstChild);       
        }
        // pruchod xml dokumentem a vkladani do selectu
        var cvs = xmlHttp.responseXML.getElementsByTagName('prvek');
        
          //tohle se pridava pouze pokud je vice cisel vykresu
          if(cvs.length>1)
          {  
             option = document.createElement("option");  //vytvoøí element typu option
             option.value = "";
             option.innerHTML = "------- vyberte -------";
             if(select)
              select.appendChild(option);
          }
        for (var i=0; i < cvs.length; i++) {
          option = document.createElement("option");  //vytvoøí element typu option
          option.value = cvs[i].firstChild.data;
          option.innerHTML = cvs[i].firstChild.data;
          if(select)
            select.appendChild(option);
        }
        
      
      prodcena = document.getElementById("prod_cena");    
      if(prodcena)
      {
         if(cvs.length==1)
         { vyber_cenu();
         }
         else prodcena.innerHTML = '';
      }
      
      nakup1 = document.getElementById("posln");
      nakup2 = document.getElementById("prumn");
      if(nakup1 && nakup2)
      {
         if(cvs.length==1)
           dej_cenu('nakup');
         else {nakup1.innerHTML = '';nakup2.innerHTML = '';}
      }
         
      kooperace = document.getElementById("poslk");
      if(kooperace)
      {
         if(cvs.length==1)
           dej_cenu('koop');
         else kooperace.innerHTML = '';
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
  cv_hotovo = true;
  
 

}

















