{% if products != '' %}
	<ul class="product-list {% if view == 0 %}list-grid{% else %}list-long{% endif %}">
		{{ products }}
	</ul>
	{% if pagination == 1 %}
		{pagination dataset=$dataset}
	{% endif %}
	
{% else %}
	<p>{% trans %}ERR_EMPTY_PRODUCT_LIST{% endtrans %}</p>
{% endif %}
						