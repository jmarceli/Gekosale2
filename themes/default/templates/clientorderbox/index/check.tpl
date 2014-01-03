{% import "forms.tpl" as forms %}
{% extends "layoutbox.tpl" %}
{% block content %}
<article class="article">
	<h1>{% trans %}TXT_ORDER_STATUS{% endtrans %}</h1>
	{% if status is defined %}
		{% if status is not null %}
	    <div class="alert alert-block alert-info">
			{% trans %}TXT_CURRENT_STATUS_ORDER{% endtrans %} #{{ status.orderid }}: <strong>{{ status.name }}</strong>
		</div>
		{% else %}
		<div class="alert alert-block alert-error">
			{% trans %}TXT_YOU_HAVE_INVALID_DATA{% endtrans %}
		</div>
		{% endif %}
	{% endif %}
	<form class="well" name="{{ form.name }}" id="{{ form.name }}" method="{{ form.method }}" action="{{ form.action }}">
		<input type="hidden" name="{{ form.submit_name }}" value="1" />
		<div class="alert alert-block alert-info">
			{% trans %}YOU_WANT_MORE_INFO{% endtrans %} - <a href="{{ path('frontend.clientlogin') }}"><strong>{% trans %}TXT_LOGIN{% endtrans %}</strong></a> {% trans %}TXT_USING_DATA_ACCOUNT{% endtrans %}
		</div>
		<fieldset>
			<div class="row-fluid">
				<div class="span5">
					{{ forms.input(form.children.email, 'span12') }}
				</div>
				<div class="span5">
					{{ forms.input(form.children.orderid, 'span12') }}
				</div>
			</div>
			{{ forms.hidden(form.children.__csrf) }}
			<div class="form-actions form-actions-clean">
				<div class="row-fluid">
					<button type="submit" class="btn btn-large btn-primary">{% trans %}TXT_CHECK_STATUS{% endtrans %}</button>
				</div>
			</div>
		</fieldset>
		{{ form.javascript }}
	</form>
	
</article>
{% endblock %}
