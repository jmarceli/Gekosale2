{% if checkRulesCart is defined %}
<div class="alert alert-block alert-success">
	{% for rule in checkRulesCart if rule.type == 0 %}
	<h4>
		<strong>{% trans %}TXT_MEET_CONDITION_FOR_DISCOUNT{% endtrans %} {% if rule.discount is defined %}{{ rule.discount }}{% endif %}</strong> 
		{% for condition in rule.conditions %}
		<br />{{ condition }}
		{% endfor %}
	</h4>
	{% endfor %}
</div>
{% endif %}