{% include 'header.tpl' %}
<section id="content" class="content layout-boxes">
{% autoescape true %}
	<div id="searchNoResults">
		<h1>
			{% trans %}TXT_SEARCH_RESULTS_FOR_QUERY{% endtrans %} <strong>"{{ phrase }}"</strong>
		</h1>
		<h2>
      {% trans %}TXT_FOUND_RESULTS{% endtrans %} <strong>0</strong>.
		</h2>
	</div>
{% endautoescape %}
</section>
{% include 'footer.tpl' %}
