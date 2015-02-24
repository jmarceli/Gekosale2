<div class="alert alert-block alert-success">
  <h3>{% trans %}TXT_ORDER_ACCEPTED{% endtrans %}</h3>
</div>
<p class="marginbt20">{% trans %}TXT_THANKS_FOR_ORDER{% endtrans %}</p>
<script>
ga('require', 'ecommerce');

ga('ecommerce:addTransaction', {
  'id': '{{ orderId }}',
  'affiliation': '{{ SHOP_NAME }}',
{% if orderData.globalPricePromo %}
  'revenue': '{{ orderData.globalPricePromo|priceFormat }}',
{% else %}
  'revenue': '{{ orderData.priceWithDispatchMethod|priceFormat }}',
{% endif %}
  'shipping': '{{ orderData.dispatchmethod.dispatchmethodcost|priceFormat }}'
});

{% for item in orderData.cart %}

ga('ecommerce:addItem', {
  'id': '{{ orderId }}',
  {% if item.attributes %}
    {% for attitem in item.attributes %}
	 'sku': '{{ attitem.idproduct }}',
     'name': '{{ attitem.name }}',
     'price': '{{ attitem.newprice|priceFormat }}',
     'quantity': '{{ attitem.qty }}'
	{% endfor %}
  {% else %}
   'sku': '{{ item.idproduct }}',
   'name': '{{ item.name }}',
   'price': '{{ item.newprice|priceFormat }}',
   'quantity': '{{ item.qty }}'
  {% endif %}
});

{% endfor %}

ga('ecommerce:send');
</script>

