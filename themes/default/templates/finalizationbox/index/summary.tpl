<tr class="special">
	<td colspan="4" class="alignright"><h4>{% trans %}TXT_AMOUNT_TOTAL{% endtrans %}</h4></td>
	{% if clientOrder.priceWithDispatchMethodPromo is not defined %}
	<td class="center"><h3>{{ clientOrder.priceWithDispatchMethod|priceFormat }}</h3></td>
	{% else %}
	<td class="center"><h3>{{ clientOrder.priceWithDispatchMethodPromo|priceFormat }}</h3></td>
	{% endif %}
</tr>
