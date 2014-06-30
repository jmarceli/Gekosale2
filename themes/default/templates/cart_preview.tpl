<a href="{{ path('frontend.cart') }}" class="inherit">
	<p>{% trans %}TXT_NUMBEROFITEM{% endtrans %}: <span>{{ count }}</span></p>
	<p>{% trans %}TXT_PRODUCT_SUBTOTAL{% endtrans %}: <span>{{ globalPrice|priceFormat }}</span></p>
</a>
