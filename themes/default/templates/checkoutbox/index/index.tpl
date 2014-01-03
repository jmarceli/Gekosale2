{% extends "layoutbox.tpl" %} 
{% block content %}
<h1 class="large">Krok 1. Dane adresowe</h1>
<div class="row-fluid row-form">
	<div class="span9">
		{% if formLogin is defined %}
    	<div class="span3 alignright">
        	<h3 class="normal font20">{% trans %}TXT_ORDER_WITH_ACCOUNT{% endtrans %}</h3>
        </div>
        <div class="span6">
        	{{ formLogin }}
		</div>
		<div class="clearfix"></div>
		{% endif %}
		<div class="span3 alignright">
        	<h3 class="normal font20">{% trans %}TXT_SHOPPING_FIRST_TIME{% endtrans %}</h3>
            <h4 class="normal font15">{% trans %}TXT_SHOPPING_AS_GUEST{% endtrans %}<br>{% trans %}TXT_OR_LOG_IN{% endtrans %}</h4>
		</div>
        <div class="span6">
        	{{ formClient }}
		</div>
	</div>
</div>
{% endblock %}
