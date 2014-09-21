{% extends "layoutbox.tpl" %}
{% block content %}
<div>
  <p>{% trans %}TXT_PAYMENT_CANCELLED_CONTACT{% endtrans %}<br>
  {% if orderId > 0 %}
  {% trans %}TXT_YOUR_ORDER_ID{% endtrans %}: <strong> {{ orderId }}</strong>
  </p>
  {% endif %}
</div>
{% endif %}
{% endblock %}
<div class="buttons">
	<a href="{{ path('frontend.home') }}" class="button"><span>{% trans %}TXT_BACK_TO_SHOPPING{% endtrans %}</span></a>
</div>	
