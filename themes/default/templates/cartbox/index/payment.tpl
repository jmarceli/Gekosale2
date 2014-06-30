<h4>{% trans %}TXT_PAYMENT_TYPE{% endtrans %}</h4>
<div class="control-group">
	{% for payment in payments %}
	<label class="radio"><input type="radio" name="paymentsradio" id="payment-{{ payment.idpaymentmethod }}" value="{{ payment.name }}" {% if payment.idpaymentmethod == checkedPayment.idpaymentmethod %}checked="checked"{% endif %} onclick="xajax_setPeymentChecked({{ payment.idpaymentmethod }}, '{{ payment.name }}'); return false;"> {{ payment.name }}</label>
	{% endfor %}
</div>