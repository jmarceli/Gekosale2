<nav id="navbar" class="navbar">
<div class="navbar-inner">
  <div class="container">
    <ul class="nav">
      <li class="dropdown {% if CURRENT_CONTROLLER == 'categorylist' or CURRENT_CONTROLLER == 'productcart' %}active{% endif %}"><a href="{{ cat.link }}" class="dropdown-toggle" data-toggle="dropdown">{% trans %}TXT_PRODUCTS{% endtrans %} <b class="caret"></b></a>
      <ul class="dropdown-menu">
        {% for category in categories %}
        <li><a href="{{ category.link }}">{{ category.label }}</a></li>
        {% endfor %}
      </ul>
      </li>
      <li class="divider-vertical"></li>
      <li class="dropdown {% if CURRENT_CONTROLLER == 'productpromotion' %}active{% endif %}"><a href="{{ path('frontend.productpromotion') }}">{% trans %}TXT_PROMOTIONS{% endtrans %}</a></li>
      <li class="divider-vertical"></li>
      <li class="dropdown {% if CURRENT_CONTROLLER == 'productnews' %}active{% endif %}"><a href="{{ path('frontend.productnews') }}">{% trans %}TXT_NEW_PRODUCTS{% endtrans %}</a></li>
      {% for cat in contentcategory if cat.header == 1 %}
      <li class="divider-vertical"></li>
      <li class="dropdown">
      <a href="{{ cat.link }}" {% if cat.children is not empty %}class="dropdown-toggle" data-toggle="dropdown"{% endif %}>
        {{ cat.name }}{% if cat.children is not empty %} <b class="caret"></b>{% endif %}
      </a>
      {% if cat.children is not empty %}
      <ul class="dropdown-menu">
        {% for subcat in cat.children if subcat.header == 1 %}
        <li><a href="{{ subcat.link }}">{{ subcat.name }}</a></li>
        {% endfor %}
      </ul>
      {% endif %}
      </li>
      {% endfor %}
    </ul>
    <form id="product-search" class="navbar-search form-search pull-right" action="{{ path('frontend.productsearch', {'action' : 'index'}) }}" method="post">
      <div class="input-append">
        <input id="product-search-phrase" name="query" type="text" class="search-query span2" placeholder="{% trans %}TXT_SEARCH_PRODUCT{% endtrans %}"><button class="btn" type="submit"><i class="icon-search"></i></button>
      </div>
    </form>
    <div id="livesearch"></div>
  </div>
</div>
</nav>
