{% for product in productCart %}
	{% if product.standard == 1 %}
	<tr>
		<td><img src="{{ product.smallphoto }}" alt=""></td>
		<td><a href="{{ path('frontend.productcart', {"param": product.seo}) }}">{{ product.name }}</a></td>
		<td>{{ product.newprice|priceFormat }}</td>
		<td><input type="text" class="spinnerhide" value="{{ product.qty }}" onchange="xajax_changeQuantity({{ product.idproduct }},null,this.value);" /></td>
		<td>{{ product.qtyprice|priceFormat }}</td>
		<td><a class="btn btn-mini" href="#" onclick="xajax_deleteProductFromCart({{ product.idproduct }}, null); return false;"><i class="icon-remove"></i></a></td>
	</tr>
	{% endif %}
	
	{% for attribprod in product.attributes if product.attributes != NULL %}
	<tr>
		<td><img src="{{ attribprod.smallphoto }}" alt=""></td>
		<td><a href="{{ path('frontend.productcart', {"param": attribprod.seo}) }}">{{ attribprod.name }}</a>
		{% for feature in attribprod.features %}
			<br />{{ feature.group }}: {{ feature.attributename }}
		{% endfor %}
		</td>
		<td>{{ attribprod.newprice|priceFormat }}</td>
		<td><input type="text" class="spinnerhide" value="{{ attribprod.qty }}" onchange="xajax_changeQuantity({{ attribprod.idproduct }},{{ attribprod.attr }},this.value);" /></td>
		<td>{{ attribprod.qtyprice|priceFormat }}</td>
		<td><a class="btn btn-mini" href="#" onclick="xajax_deleteProductFromCart({{ attribprod.idproduct }}, {{ attribprod.attr }}); return false;"><i class="icon-remove"></i></a></td>
	</tr>
	{% endfor %}
{% endfor %}	