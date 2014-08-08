<tr>
	<td colspan="2"><h4>{% trans %}TXT_TRANSPORT_AND_PAYMENT{% endtrans %}:</h4>{{ clientOrder.dispatchmethod.dispatchmethodname }} - {{ clientOrder.payment.paymentmethodname }}</td>
	<td colspan="2" class="alignright">{% trans %}TXT_COST_OF_DELIVERY{% endtrans %}</td>
	<td class="center"><strong>{{ clientOrder.dispatchmethod.dispatchmethodcost|priceFormat }}</strong></td>
</tr>
