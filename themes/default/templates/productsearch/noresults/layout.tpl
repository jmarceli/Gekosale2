{% include 'header.tpl' %}
<section id="content" class="content layout-boxes">
{% autoescape true %}
	<div id="searchNoResults">
		<h1>
			Wynik wyszukiwania dla <strong>"{{ phrase }}"</strong>
		</h1>
		<h2>
			Dla "{{ phrase }}" znaleziono <strong>0</strong> pozycji.
		</h2>
	</div>
{% endautoescape %}
</section>
{% include 'footer.tpl' %}
