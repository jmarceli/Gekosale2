</div>
<footer class="footer">
<div class="container border">
  <div class="row">
    <div class="span12">
      <div class="row-fluid">
        {% if contentcategory is not empty %}
        {% for cat in contentcategory if cat.footer == 1 %}
        <div class="span4">
          <h3 class="font">
            {% if cat.children is empty %}<a href="{{ cat.link }}">{% endif %}
              {{ cat.name }}
            {% if cat.children is empty %}</a>{% endif %}
          </h3>
          <ul class="nav nav-pills nav-stacked">
            {% if cat.children is not empty %}
            {% for subcat in cat.children if subcat.footer == 1 %}
            <li><a href="{{ subcat.link }}">{{ subcat.name }}</a></li>
            {% endfor %}
            {% endif %}
          </ul>
        </div>
        {% endfor %}
        {% endif %}
        <div class="span4">
          <h3 class="font">{% trans %}TXT_YOUR_ACCOUNT{% endtrans %}</h3>
          <ul class="nav nav-pills nav-stacked">
            {% if client is not empty %}
            <li><a href="{{ path('frontend.clientsettings') }}">{% trans %}TXT_SETTINGS{% endtrans %}</a></li>
            <li><a href="{{ path('frontend.clientaddress') }}">{% trans %}TXT_CLIENT_ADDRESS{% endtrans %}</a></li>								
            {% else %}
            <li><a href="{{ path('frontend.clientlogin') }}">{% trans %}TXT_LOGIN_TO_YOUR_ACCOUNT{% endtrans %}</a></li>
            <li><a href="{{ path('frontend.registration') }}">{% trans %}TXT_REGISTER{% endtrans %}</a></li>
            {% endif %}
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container copy">
  <div class="row">
    <div class="span6">
      {{ "now"|date("Y") }} © <span>{{ SHOP_NAME }}</span> / {% trans %}TXT_ALL_RIGHTS_RESERVED{% endtrans %}.
    </div>
    <div class="span6 pull-right alignright">
      <a href="http://www.gekosale.pl/" title="{% trans %}TXT_GEKOSALE_OS{% endtrans %}" target="_blank"><img src="{{ DESIGNPATH }}_images_frontend/core/logos/logo-mini.png" alt="Gekosale" /></a>
    </div>
  </div>
</div>
</footer>
<div id="basketModal" class="modal fade hide"></div>
<div id="productModal" class="modal fade hide"></div>
{% include 'modal_gallery.tpl' %}
{{ footerJS }}
{{ affirmeo }}
{% if modulesettings.ceneo.ceneoguid != ''%}
<script type="text/javascript" src="http://ssl.ceneo.pl/shops/v3/script.js?accountGuid={{ modulesettings.ceneo.ceneoguid }}"></script>
{% endif %}
<link rel="stylesheet" href="{{ css_asset('css/divante.cookies.min.css') }}" type="text/css"/>
<script>window.jQuery.cookie || document.write('<script src="{{ DESIGNPATH }}_js_libs/jquery.cookie.min.js"><\/script>')</script>
<script type="text/javascript">
  (function(a){a.divanteCookies={render:function(b){var c="";c+='<div id="cookiesBar"><div id="cookiesBarWrap"><p>{% trans %}TXT_COOKIE_FIRST{% endtrans %} <a href="{% trans %}TXT_COOKIE_POLICY_URL{% endtrans %}" title="{% trans %}TXT_COOKIE_POLICY{% endtrans %}">{% trans %}TXT_COOKIE_POLICY{% endtrans %}</a>.</p></p><p>{% trans %}TXT_COOKIE_SECOND{% endtrans %}</p><a id="cookiesBarClose" href="#" title="{% trans %}TXT_COOKIE_POLICY{% endtrans %}">{% trans %}TXT_COOKIE_POLICY{% endtrans %}</a></div></div>',a.cookie("cookie")||(a("body").append(c),a.fn.delegate?a("#cookiesBar").delegate("#cookiesBarClose","click",function(b){a.divanteCookies.closeCallback(b)}):a("#cookiesBarClose").bind("click",function(b){a.divanteCookies.closeCallback(b)}))},closeCallback:function(b){return a("#cookiesBar").fadeOut(),a.cookie("cookie")||a.cookie("cookie",!0,{path:"/",expires:30}),b.preventDefault(),!1}}})(jQuery);

  jQuery.divanteCookies.render();
</script>
</body>
</html>
