<tr>
  <td colspan="2" class="order-method">
    <h4>{% trans %}TXT_DELIVERY_TYPE{% endtrans %}</h4>
    <div class="control-group">
      {% for delivery in deliverymethods %}
      <label class="radio">
        <input type="radio" name="optionsRadios" id="delivery-{{ delivery.dispatchmethodid }}" value="{{ delivery.dispatchmethodid }}"  {% if delivery.dispatchmethodid == checkedDelivery.dispatchmethodid %}checked="checked"{% endif %} onclick="xajax_setDispatchmethodChecked({{ delivery.dispatchmethodid }}); return false;"> {{ delivery.name }} <span class="pull-right"><strong>{{ delivery.dispatchmethodcost|priceFormat }}</strong></span>
      </label>
      {% endfor %}
    </div>
    <h4>{% trans %}TXT_PAYMENT_TYPE{% endtrans %}</h4>
    <div class="control-group">
      {% for payment in payments %}
      <label class="radio"><input type="radio" name="paymentsradio" id="payment-{{ payment.idpaymentmethod }}" value="{{ payment.name }}" {% if payment.idpaymentmethod == checkedPayment.idpaymentmethod %}checked="checked"{% endif %} onclick="xajax_setPeymentChecked({{ payment.idpaymentmethod }}, '{{ payment.name }}'); return false;"> {{ payment.name }}</label>
      {% endfor %}
    </div>
  </td>
  <td colspan="2" class="alignright">{% trans %}TXT_DELIVERY_COST{% endtrans %}</td>
  <td colspan="2" class="center"><strong>{{ checkedDelivery.dispatchmethodcost|priceFormat }}</strong></td>
</tr>
