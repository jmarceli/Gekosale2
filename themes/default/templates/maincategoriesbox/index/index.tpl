{% extends "layoutbox.tpl" %}
{% block content %}
{% if categories|length == 0 %}
	<p>{% trans %}ERR_EMPTY_MENUCATEGORY{% endtrans %}</p>
{% else %}
<ul class="nav nav-pills nav-stacked cat-list">
  {% for category in categories if exclude|length == 0 or category.id in exclude %}
  {% if category.totalproducts %}
  <li><a href="{{ category.link }}" title="{{ category.label }}">{{ category.label }}</a></li>
  {% endif %}
  {% endfor %}
  <li class="all"><a href="{{ path('frontend.sitemap') }}">{% trans %}TXT_ALL_CATEGORIES{% endtrans %}</a></li>
</ul>
{% endif %}
{% endblock %}
