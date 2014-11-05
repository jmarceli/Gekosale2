	<ul id="productTab" class="nav nav-tabs">
		{% if product.description != '' %} 
		<li class="active"><a href="#product-description" data-toggle="tab">{% trans %}TXT_DESCRIPTION{% endtrans %}</a></li>
		{% endif %}
		{% if product.longdescription != '' %} 
		<li><a href="#product-longdescription" data-toggle="tab">{% trans %}TXT_ADDITIONAL_INFO{% endtrans %}</a></li>
		{% endif %}
		{% if files|length > 0 %}
		<li><a href="#files" data-toggle="tab">{% trans %}TXT_FILES{% endtrans %}</a></li>
		{% endif %}
		<li><a id="toggle-reviews" href="#reviews" data-toggle="tab">{% trans %}TXT_OPINIONS{% endtrans %}</a></li>
	</ul>
	<div id="productTabContent" class="tab-content">
	{% if product.description != '' %} 
	<div class="tab-pane fade active in product-details" id="product-description" itemprop="description">
		{{ product.description }}
	</div>
	{% endif %}
	{% if product.longdescription != '' %} 
	<div class="tab-pane fade product-details" id="product-longdescription">
		{{ product.longdescription }}
	</div>
	{% endif %}
	{% if files|length > 0 %}
	<div class="tab-pane fade product-details" id="files">
		{% include 'productbox/index/files.tpl' %}
	</div>
	{% endif %}
	<div class="tab-pane fade product-details" id="reviews">
		{% include 'productbox/index/opinions.tpl' %}
	</div>
</div>
