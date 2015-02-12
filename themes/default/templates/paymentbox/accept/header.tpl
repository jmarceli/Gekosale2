<div class="alert alert-block alert-success">
  <h3>{% trans %}TXT_ORDER_ACCEPTED{% endtrans %}</h3>
</div>
<p class="marginbt20">{% trans %}TXT_THANKS_FOR_ORDER{% endtrans %}</p>
<script>
ga('require', 'ecommerce');

ga('ecommerce:addTransaction', {
  'id': '{{ orderId }}',
  'affiliation': '{{ SHOP_NAME }}',
  'revenue': '{{ orderData.priceWithDispatchMethod|priceFormat }}',
  'shipping': '{{ orderData.dispatchmethod.dispatchmethodcost|priceFormat }}'
});

{% for item in orderData.cart %}

ga('ecommerce:addItem', {
  'id': '{{ orderId }}',
  'sku': '{{ item.idproduct }}',
  'name': '{{ item.name }}',
  'price': '{{ item.newprice|priceFormat }}',
  'quantity': '{{ item.qty }}'
});

{% endfor %}

ga('ecommerce:send');
</script>