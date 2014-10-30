{% extends "layoutbox.tpl" %}
{% block content %}
<div class="row-fluid row-form">
  <h1 class="large">{% trans %}TXT_STEP_3{% endtrans %}</h1>
  <div class="span11">
    {% include 'paymentbox/accept/header.tpl' %}

    {% include 'paymentbox/accept/info.tpl' %}
    {% include 'paymentbox/accept/footer.tpl' %}
  </div>
</div>
{% endblock %}
