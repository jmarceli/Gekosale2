{% extends "layout.tpl" %}
{% block javascript %}
{{ parent() }}
<script type="text/javascript" src="{{ DESIGNPATH }}_js_libs/swf.packed.js?v={{ appVersion }}"></script>
{% endblock %}
{% block content %}
<h2><img src="{{ DESIGNPATH }}_images_panel/logos/action.png" alt=""/>Integracja z Action</h2>
{% if disabled is defined %}
	<div class="block">
		<p><img src="{{ DESIGNPATH }}_images_panel/logos/action_logo.gif" /></p>
		<p>Nie uzupełniłeś danych partnera Action w konfiguracji modułu. Przejdź do <a href="{{ path('admin', {"controller": "globalsettings", "action": "index"}) }}">Globalnej konfiguracji</a>, a następnie w zakładce Integracja Action wprowadź dane partnera.</p>
		<p>Jeżeli nie posiadasz jeszcze dostępów do i-serwis'u Action, skontaktuj się ze swoim opiekunem.</p>
	</div>
{% else %}
	<ul class="possibilities">
		<li><a id="download" href="#" class="button"><span><img src="{{ DESIGNPATH }}_images_panel/icons/buttons/save.png" alt=""/>Pobierz aktualny plik XML</span></a></li>
	</ul>
	{% if success %}
	<script type="text/javascript">
	$(document).ready(function(){
		GMessage('{% trans %}TXT_JS_PROGRESS_INDICATOR_SUCCESS{% endtrans %}', '{{ success }}');
	});
	</script>
	{% endif %}
	{{ form }}
	<script>
	
	$(document).ready(function(){
		$('#download').click(function(){
			xajax_doDownloadBigXML();
			return false;
		});
	});
	</script>
{% endif %}
{% endblock %}