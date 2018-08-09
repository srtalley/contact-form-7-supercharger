function createCookie(name, value, days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    var expires = "; expires=" + date.toGMTString();
  }
  else var expires = "";

  document.cookie = name + "=" + value + expires + "; path=/";
}


function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}

function eraseCookie(name) {
  createCookie(name, "", -1);
}

function getQueryVariable(variable)
{
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    if(pair[0] == variable){return pair[1];}
  }
  return(false);
}

//http://ds-web01.home:16081/test/?utm_source=facebook&utm_medium=cpc&utm_campaign=colorado&utm_term=hoary&utm_content=pitcher

// Get the query variables and set a cookie
if(getQueryVariable('utm_source') != '' ) {

  //Get the date and time
  var currentDate = new Date();

  createCookie('_ds_utmz',
                'utm_source|' + getQueryVariable('utm_source') + ',' +
                'utm_medium|' +  getQueryVariable('utm_medium') + ',' +
                'utm_campaign|' + getQueryVariable('utm_campaign') + ',' +
                'utm_term|' + getQueryVariable('utm_term') + ',' +
                'utm_content|' + getQueryVariable('utm_content') + ',' +
                'utm_landing_page|' + window.location.href + ',' +
                'utm_landing_page_date|' + currentDate, 30);

}
