{% extends "layoutbox.tpl" %} 
{% block content %}
<h1 class="large">Krok 1. Dane adresowe</h1>
<div class="row-fluid row-form">
	<div class="span9">
		{% if formLogin is defined %}
    	<div class="span3 alignright">
        	<h3 class="normal font20">Posiadam konto</h3>
        </div>
        <div class="span6">
        	{{ formLogin }}
		</div>
		<div class="clearfix"></div>
		{% endif %}
		<div class="span3 alignright">
        	<h3 class="normal font20">Kupuję pierwszy raz</h3>
            <h4 class="normal font15">Kupuj jako gość<br>lub zarejestruj się</h4>
		</div>
        <div class="span6">
        	{{ formClient }}
		</div>
	</div>
</div>
{% endblock %}
