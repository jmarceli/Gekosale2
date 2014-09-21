<tr class="special">
	<td colspan="4" class="alignright"><h4>{% trans %}TXT_TOTAL_TO_PAY{% endtrans %}</h4>{% trans %}TXT_PAYMENT_METHOD{% endtrans %}: <strong>{{ checkedPayment.paymentmethodname }}</strong></td>
	{% if order.priceWithDispatchMethodPromo is not defined %}
	<td colspan="2" class="center"><h3>{{ order.priceWithDispatchMethod|priceFormat }}</h3></td>
	{% else %}
	<td colspan="2" class="center"><h3>{{ order.priceWithDispatchMethodPromo|priceFormat }}</h3></td>
	{% endif %}
</tr>
