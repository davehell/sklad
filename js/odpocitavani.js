/**
 * automaticke odhlasovani na klientovi (pokud vyply javascript realizuje to i server)
 *
 */  
// JavaScript Document

var expires  = new Date(0,0,0,0,30,0)
var zero = new Date(0,0,0,0,0,0)
expired = false;

function countDown()
{
 now = new Date()
 expires.setTime(expires.getTime()-1000) //nastaveni na 30 minut
 showTime(expires);
 
 if(zero.getTime()==expires.getTime())
   window.location.href = "odhlaseni.php?type=auto";
 window.setTimeout("countDown()", 1000); 
}


function showTime(mytime)
{
  minutes = mytime.getMinutes();
  seconds = mytime.getSeconds();
  
  if (minutes < 10) minutes = "0"+minutes;
  if (seconds < 10) seconds = "0"+seconds;
  
 
 element = document.getElementById("odpocitavani");
 element.innerHTML = minutes+":"+seconds;
 //element.innerHTML = "jede";

}

