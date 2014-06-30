<fieldset class="listing">
	<legend><span>{% trans %}TXT_VIEW_ORDER_SUMMARY{% endtrans %}</span></legend>
	{% if productCart is defined %}
	<dl>
	{% for s in summary %}
		<dt>{{ s.label }}</dt><dd>{{ s.value }}</dd>
	{% endfor %}
	</dl>
	{% endif %}
</fieldset>