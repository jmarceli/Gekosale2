{% extends "layoutbox.tpl" %}
{% block content %}
<div class="row-fluid row-form">
  <h1 class="large">{% trans %}TXT_STEP_3{% endtrans %}</h1>
  <div class="span11">
    {% include 'paymentbox/accept/header.tpl' %}

    <h4 class="font15">{% trans %}TXT_MAKE_PAYMENT{% endtrans %}</h4>
    <p class="marginbt20">{% trans %}TXT_ORDER_AFTER_PAYMENT{% endtrans %} {% trans %}TXT_ORDER_PAYMENT_DATA{% endtrans %}:</p>
    <p class="marginbt20">
    <strong>{% trans %}TXT_BANK_NAME{% endtrans %}:</strong> {{ content.bankname }}<br />
    <strong>{% trans %}TXT_BANK_ACC_NUMBER{% endtrans %}:</strong> {{ content.bankacct }}<br />
    <strong>{% trans %}TXT_TITLE{% endtrans %}:</strong> {% trans %}TXT_ORDER{% endtrans %} {{ orderId }}
    </p>

    {% include 'paymentbox/accept/info.tpl' %}
    {% include 'paymentbox/accept/footer.tpl' %}
  </div>
</div>
{% endblock %}			
