<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
  <head>
    <title>{{ SHOP_NAME }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <base href="{{ DESIGNPATH }}">
    <!-- <meta http-equiv="refresh" content="5; URL={{ BASE_URL }}"> -->
    <link rel="shortcut icon" href="_images/favicon.ico"/>
    <link rel="stylesheet" href="_css_frontend/install.css" type="text/css"/>
  </head>
  <body>

    <div id="header">
      <div class="container">
        <h1><a href="http://www.gekosale.pl">{% trans %}TXT_GEKOSALE_OS{% endtrans %}</a></h1>
      </div>

    </div>
    <div id="content">
      <div class="container">
        <div class="block block-flat">
          <div class="nw"></div>
          <div class="ne"></div>
          <div class="se"></div>
          <div class="sw"></div>
          <div class="block-content">
            <form id="tabs-1" class="panel-contents">
              <h3>{% trans %}TXT_CRITICAL_ERROR{% endtrans %}</h3>
              <div class="hint"> 
                <pre>{{ error }}</pre> 
              </div> 
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>

