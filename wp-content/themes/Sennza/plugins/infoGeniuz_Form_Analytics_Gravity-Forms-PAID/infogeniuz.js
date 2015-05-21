var DetectBrowser = {  
 init: function () {  
  this.browser = this.searchString(this.dataBrowser) || "An unknown browser";  
  this.version = this.searchVersion(navigator.userAgent)  
   || this.searchVersion(navigator.appVersion)  
   || "an unknown version";          
 },  
 searchString: function (data){  
  for (var i=0;i<data.length;i++) {  
   var dataString = data[i].string;  
   var dataProp = data[i].prop;  
   this.versionSearchString = data[i].versionSearch || data[i].identity;  
   if(dataString){  
	if (dataString.indexOf(data[i].subString) != -1)  
		return data[i].identity;  
   }  
   else if (dataProp)  
		return data[i].identity;  
  }  
 },  
 searchVersion: function (dataString) {  
  var index = dataString.indexOf(this.versionSearchString);  
  if (index == -1) return;  
  return parseFloat(dataString.substring(index+this.versionSearchString.length+1));  
 },  
 dataBrowser: [  
  {  
   string: navigator.userAgent,  
   subString: "Chrome",  
   identity: "Chrome"  
  },  
  {  string: navigator.userAgent,  
   subString: "OmniWeb",  
   versionSearch: "OmniWeb/",  
   identity: "OmniWeb"  
  },  
  {  
   string: navigator.vendor,  
   subString: "Apple",  
   identity: "Safari",  
   versionSearch: "Version"  
  },  
  {  
   prop: window.opera,  
   identity: "Opera"  
  },  
  {  
   string: navigator.vendor,  
   subString: "iCab",  
   identity: "iCab"  
  },  
  {  
   string: navigator.vendor,  
   subString: "KDE",  
   identity: "Konqueror"  
  },  
  {  
   string: navigator.userAgent,  
   subString: "Firefox",  
   identity: "Firefox"  
  },  
  {  
   string: navigator.vendor,  
   subString: "Camino",  
   identity: "Camino"  
  },  
  {  // for newer Netscapes (6+)  
   string: navigator.userAgent,  
   subString: "Netscape",  
   identity: "Netscape"  
  },  
  {  
   string: navigator.userAgent,  
   subString: "MSIE",  
   identity: "Internet Explorer",  
   versionSearch: "MSIE"  
  },  
  {  
   string: navigator.userAgent,  
   subString: "Gecko",  
   identity: "Mozilla",  
   versionSearch: "rv"  
  },  
  {   // for older Netscapes (4-)  
   string: navigator.userAgent,  
   subString: "Mozilla",  
   identity: "Netscape",  
   versionSearch: "Mozilla"  
  }  
 ]  
   
};  
DetectBrowser.init();  
<!-- End Hardware Data -->

<!-- Start Geolocation Data -->
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
   document.write("<script src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'>" + "</sc" + "ript>"); 

if( typeof(_gat) != "undefined" ) {
	var pageTracker = _gat._getTracker("UA-1-1");
	pageTracker._trackPageview();
}

function _uGC(l,n,s) {
 if (!l || l=="" || !n || n=="" || !s || s=="") return "-";
 var i,i2,i3,c="-";
 i=l.indexOf(n);
 i3=n.indexOf("=")+1;
 if (i > -1) {
  i2=l.indexOf(s,i); if (i2 < 0) { i2=l.length; }
  c=l.substring((i+i3),i2);
 }
 return c;
}

function load()
{

document.getElementById('igz_g_city').value = geoip_city();
document.getElementById('igz_g_country').value = geoip_country_name();
document.getElementById('igz_g_state').value = geoip_region_name();
document.getElementById('igz_g_longitude').value = geoip_longitude();
document.getElementById('igz_g_latitude').value = geoip_latitude();
document.getElementById('igz_h_browser').value = DetectBrowser.browser;
document.getElementById('igz_h_brversion').value = DetectBrowser.version;
<!-- End Geolocation Data -->

<!-- Start Analytics Data -->
var z = _uGC(document.cookie, '__utmz=', ';'); 

var source  = _uGC(z, 'utmcsr=', '|'); 
var medium  = _uGC(z, 'utmcmd=', '|'); 
var term    = _uGC(z, 'utmctr=', '|'); 
var content = _uGC(z, 'utmcct=', '|'); 
var campaign = _uGC(z, 'utmccn=', '|'); 
var gclid   = _uGC(z, 'utmgclid=', '|'); 

if (gclid !="-") { 
	  source = 'google'; 
	  medium = 'cpc'; 
} 
var csegment = _uGC(document.cookie, '__utmv=', ';'); 
if (csegment != '-') { 
	  var csegmentex = /[1-9]*?\.(.*)/;
	  csegment    = csegment.match(csegmentex); 
	  csegment    = csegment[1]; 
} else { 
	  csegment = '(not set)'; 
} 
 
var a = _uGC(document.cookie, '__utma=', ';');
var aParts = a.split(".");
var fVisits = aParts[2];
var pVisits = aParts[3];
var cVisits = aParts[4];
var nVisits = aParts[5];


var first = new Date(fVisits*1000);
document.getElementById('igz_t_original').value = first;
var previous = new Date(pVisits*1000);
document.getElementById('igz_t_previous').value = previous;
var current = new Date(cVisits*1000);
document.getElementById('igz_t_current').value = current;


var a = _uGC(document.cookie, '__utmb=', ';');

var aParts = a.split(".");
var pViews = aParts[1];
term=term.split('%20');
var no = term.length;
aa='';
for(var i=0; i<no; i++){
	aa=aa+' '+term[i];
}
term=aa;
document.getElementById('igz_a_pageviews').value = pViews;

document.getElementById('igz_a_source').value  = source; 
document.getElementById('igz_a_medium').value  = medium; 
document.getElementById('igz_a_term').value    = term; 
document.getElementById('igz_a_content').value = content; 
document.getElementById('igz_a_campaign').value = campaign; 
document.getElementById('igz_a_segment').value = csegment; 
document.getElementById('igz_a_visits').value = nVisits;
var today = new Date();
document.getElementById('igz_t_time').value = today;

}
window.onload=function(){ load();}
