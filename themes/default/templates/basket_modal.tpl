<button class="close" data-dismiss="modal">&times;</button>
<div class="alert alert-block alert-success">
	<p>"{{ product.productname }}" {% trans %}TXT_CART_PRODUCT_ADDED{% endtrans %}</p>
</div>
<div class="modal-footer form-actions-clean">
	<a href="#" class="btn  pull-left" data-dismiss="modal"><i class="icon-arrow-left"></i> Kontynuuj zakupy</a>
	<a href="{{ path('frontend.cart') }}" class="btn btn-inverse"><i class="icon-shopping-cart icon-white"></i> {% trans %}TXT_GOTO_CART{% endtrans %}</a>
</div>

