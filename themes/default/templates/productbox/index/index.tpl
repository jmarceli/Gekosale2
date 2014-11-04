{% extends "layoutbox.tpl" %}
{% block content %}
{% if draft is defined %}
<div class="alert alert-info">
	{{ draft }}
</div>
{% endif %} 
<article id="productInfo" class="article marginbt20" itemscope itemtype="http://data-vocabulary.org/Product">
  <div class="row-fluid row-form">
    <div class="span9">
      <div class="row-fluid">
        <div class="span6">
          <ul class="pagination">
            {% if product.previous != NULL %}
            <li><a href="{{ path('frontend.productcart', {"param": product.previous}) }}">← Poprzedni produkt</a></li>
            {% endif %}
            {% if product.next != NULL %}
            <li><a href="{{ path('frontend.productcart', {"param": product.next}) }}">Kolejny produkt →</a></li>
            {% endif %}
          </ul>
          <div id="gallery" data-toggle="modal-gallery" data-target="#modal-gallery">
            <div class="image-large">
              <a href="{{ product.mainphoto.large }}" rel="gallery">
                <img src="{{ product.mainphoto.normal }}" alt="{{ product.productname|e }}" itemprop="image">
              </a>
            </div>
            {% if product.otherphoto.small|length > 0%}
            <a href="#" class="slider-move slider-moveLeft"></a>
            <div class="image-slider">
              <ul>
                <li><a href="{{ product.mainphoto.large }}" title="" rel="gallery"><img src="{{ product.mainphoto.normal }}" alt="" ></a></li>
                {% for key, otherphoto in product.otherphoto.small %}
                <li><a href="{{ product.otherphoto.large[key] }}" title="" rel="gallery"><img src="{{ otherphoto }}" alt="" ></a></li>
                {% endfor %}
              </ul>
            </div>
            <a href="#" class="slider-move slider-moveRight"></a>
            {% endif %}
          </div>
        </div>
        <div class="span6">
          <h1 class="product-name" itemprop="name">{{ product.productname }}</h1>
          <ul class="labels unstyled">
            {% if product.discountprice != NULL %}
            <li class="label-promotion">{% trans %}TXT_PROMOTION{% endtrans %}</li>
            {% endif %}
            {% if product.new == 1 %}
            <li class="label-new">{% trans %}TXT_NEW_PRODUCT{% endtrans %}</li>
            {% endif %}
            {% for status in product.statuses %}
            <li class="label{% if status.symbol != '' %}-{{ status.symbol }}{% endif %}">{{ status.name }}</li>
            {% endfor %}
          </ul>
          {% if product.opinions > 0 %}
          <div class="product-star" itemprop="review" itemscope itemtype="http://data-vocabulary.org/Review-aggregate">
            <div class="star pull-left readonly" itemprop="rating" data-rating="{{ product.rating }}">{{ product.rating }}</div>
            <span class="info pull-left">(<strong>{{ product.rating }}</strong>/5) <a href="#review" title="">Opinie (<span itemprop="count">{{ product.opinions }}</span>)</a></span>
          </div>
          {% endif %}
          <div class="intro">
            <div itemprop="description">{{ product.shortdescription }}</div>
            {% if product.producername != '' %}
            <p itemprop="brand">{% trans %}TXT_PRODUCER{% endtrans %}: <a href="{{ path('frontend.producerlist', {"param": product.producerurl}) }}" title="{{ product.producername|e }}"><strong>{{ product.producername }}</strong></a></p>
            {% endif %}
          </div>
        </div>
      </div>
    </div>
    <div class="span3">
      <div id="addToCart" class="well well-small" itemprop="offerDetails" itemscope itemtype="http://data-vocabulary.org/Offer">
        {% if ( product.discountprice != NULL and product.discountprice != product.price ) %}
        <span class="hide" itemprop="price">{% if showtax == 0 %}{{ product.discountpricenetto|number_format(2) }}{% else %}{{ product.discountprice|number_format(2) }}{% endif %}</span>
        <span class="price price-large" id="changeprice{% if showtax == 0 %}-netto{% endif %}">{% if showtax == 0 %}{{ product.discountpricenetto|priceFormat }}{% else %}{{ product.discountprice|priceFormat }}{% endif %}</span>
        <span class="price price-small" id="changeprice{% if showtax == 0 %}-netto{% endif %}-old">{% if showtax == 0 %}{{ product.pricenetto|priceFormat }}{% else %}{{ product.price|priceFormat }}{% endif %}</span>
        {% else %}
        <span class="hide" itemprop="price">{% if showtax == 0 %}{{ product.pricewithoutvat|number_format(2) }}{% else %}{{ product.price|number_format(2) }}{% endif %}</span>
        <span class="price price-large" id="changeprice{% if showtax == 0 %}-netto{% endif %}">{% if showtax == 0 %}{{ product.pricewithoutvat|priceFormat }}{% else %}{{ product.price|priceFormat }}{% endif %}</span>
        {% endif %}
        <span class="hide" itemprop="currencySymbol">{{ currencySymbol }}</span>
        <div class="hr"></div>
        <ul>
          {% if product.trackstock == 1 %}
          <li><span>{% trans %}TXT_PRODUCT_IS_AVAILABLE{% endtrans %} <strong class="green available">{% trans %}TXT_AVAILABLE{% endtrans %}</strong><strong class="noavailable red">{% trans %}TXT_NOT_AVAILABLE{% endtrans %}</strong></span></li>
          {% else %}
          <li><span>{% trans %}TXT_PRODUCT_IS_AVAILABLE{% endtrans %} <span class="green"><strong>dostępny</strong></span></span></li>
          {% endif %}
          <li><span>{% trans %}TXT_DISPATCH{% endtrans %}: <strong>{% trans %}TXT_FROM{% endtrans %} {{ deliverymin|priceFormat }}</strong></span></li>
          {% if product.availablityname != '' %}
          <li><span>{% trans %}TXT_DELIVERY_IN{% endtrans %} <strong id="availablity">{{ product.availablityname }}</strong></span></li>
          {% endif %}
        </ul>
        <div class="hr"></div>
        <form>
          <input type="hidden" id="attributevariants" value="0" />
          <input type="hidden" id="availablestock" value="{{ product.stock }}" />
          <input type="hidden" id="variantprice" value="{{ product.price }}" />	
          {% if attset != NULL %}
          {% for grid, attributesgroup in attributes %}
          <label>{{ attributesgroup.name }}:</label>
          <select id="attributes-{{ grid }}" name="attributes-{{ grid }}" class="attributes span12">
            {% for v, variant in attributesgroup.attributes %}
            <option value="{{ v }}">{{ variant }}</option>
            {% endfor %}
          </select>
          {% endfor %}
          <div class="hr"></div>
          {% endif %}	
          <div class="amount">
            <div class="pull-left mgr5">{% trans %}TXT_QUANTITY{% endtrans %}:</div>
            <div class="pull-left mgr5"><input type="text" class="spinnerhide" data-packagesize="{{ product.packagesize }}" id="product-qty" value="{{ product.packagesize }}"></div>
            <div class="pull-left">{{ product.unit }}</div>
            <div class="clearfix"></div>
          </div>
          <button type="submit" id="add-cart" class="btn btn-primary btn-large available"><i class="icon-shopping-cart icon-white"></i> {% trans %}TXT_ADD_TO_CART{% endtrans %}</button>
          <a href="{{ path('frontend.contact', {"param": product.idproduct}) }}" class="btn btn-large btn-danger noavailable"><i class="icon-question-sign icon-white"></i> {% trans %}TXT_REQUEST_QUOTE{% endtrans %}</a>
        </form>
      </div>
    </div>
  </div>
</article>
<script type="text/javascript">
$(document).ready(function(){

	var producttrackstock = {{ product.trackstock }};

	$('#add-cart').unbind('click').bind('click', function(){
		if(producttrackstock == 1){
			if($('#availablestock').val() > 0){
				return xajax_addProductToCart({{ product.idproduct }}, $('#attributevariants').val(), $('#product-qty').val());
			}else{
				GError('{% trans %}ERR_SHORTAGE_OF_STOCK{% endtrans %}');
				return false;
			}
		}else{
			return xajax_addProductToCart({{ product.idproduct }}, $('#attributevariants').val(), $('#product-qty').val());
		}
	});
	
	{% if attset != NULL %}
	GProductAttributes({
		aoVariants: {{ variants }},
		bTrackStock: producttrackstock,
	});
	{% else %}
	if(producttrackstock == 1 && ($('#availablestock').val() == 0)){
		$('.available').hide();
		$('.noavailable').show();
	}else{
		$('.available').show();
		$('.noavailable').hide();
	}
	{% endif %}
});

</script>
{% endblock %}
