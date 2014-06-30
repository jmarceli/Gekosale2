<h4>{% trans %}TXT_DELIVERY_TYPE{% endtrans %}</h4>
<div class="control-group">
	{% for delivery in deliverymethods %}
	<label class="radio">
		<input type="radio" name="optionsRadios" id="delivery-{{ delivery.dispatchmethodid }}" value="{{ delivery.dispatchmethodid }}"  {% if delivery.dispatchmethodid == checkedDelivery.dispatchmethodid %}checked="checked"{% endif %} onclick="xajax_setDispatchmethodChecked({{ delivery.dispatchmethodid }}); return false;"> {{ delivery.name }} <span class="pull-right"><strong>{{ delivery.dispatchmethodcost|priceFormat }}</strong></span>
	</label>
	{% endfor %}
</div>