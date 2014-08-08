	</div>
    <footer class="footer">
        <div class="container border">
            <div class="row">
                <div class="span12">
                    <div class="row-fluid">
                    	{% if contentcategory is not empty %}
						{% for cat in contentcategory if cat.footer == 1 %}
                        <div class="span4">
                            <h3 class="font">{{ cat.name }}</h3>
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
                    2013 © <span>{{ SHOP_NAME }}</span> / Wszystkie prawa zastrzeżone.
                </div>
                <div class="span6 pull-right alignright">
                    <a href="http://www.gekosale.pl/" title="Gekosale.pl - bezpłatne oprogramowanie sklepu internetowego" target="_blank"><img src="{{ DESIGNPATH }}_images_frontend/core/logos/logo-mini.png" alt="Gekosale" /></a>
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
  <script type="text/javascript" src="{{ css_asset('js/divante.cookies.min.js') }}"></script>
  <link rel="stylesheet" href="{{ css_asset('css/divante.cookies.min.css') }}" type="text/css"/>
  <script>window.jQuery.cookie || document.write('<script src="{{ DESIGNPATH }}_js_libs/jquery.cookie.min.js"><\/script>')</script>
  <script type="text/javascript">
    jQuery.divanteCookies.render({
        privacyPolicy : false,
        cookiesPageURL : '{{ URL }}regulamin'
    });
  </script>
</body>
</html>
