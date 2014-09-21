{% extends "layoutbox.tpl" %}
{% block content %}
<div class="row-fluid row-form">
  <h1 class="large">{% trans %}TXT_STEP_3{% endtrans %}</h1>
  <div class="span11">
    <div class="alert alert-block alert-success">
      <h3>{% trans %}TXT_PAYMENT_SERVICE_REDIRECT{% endtrans %}</h3>
    </div>
  </div>
</div>
<form id="paypal" action="{{ content.gateway }}" method="POST">
  <input type="hidden" name="rm" value="{{ content.rm }}">
  <input type="hidden" name="cmd" value="{{ content.cmd }}">
  <input type="hidden" name="business" value="{{ content.business }}">
  <input type="hidden" name="currency_code" value="{{ content.currency_code }}">
  <input type="hidden" name="return" value="{{ content.return }}">
  <input type="hidden" name="cancel_return" value="{{ content.cancel_return }}">
  <input type="hidden" name="notify_url" value="{{ content.notify_url }}">
  <input type="hidden" name="item_name" value="{{ content.item_name }}">
  <input type="hidden" name="amount" value="{{ content.amount }}">
  <input type="hidden" name="item_number" value="{{ content.item_number }}">
  <input type="hidden" name="custom" value="{{ content.session_id }}">
</form>
<script type="text/javascript">

$(document).ready(function(){
	$('#paypal').submit();
});

</script>
{% endblock %}
