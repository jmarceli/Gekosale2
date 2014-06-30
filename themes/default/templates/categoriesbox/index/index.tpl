{% extends "layoutbox.tpl" %}
{% block content %}
{% if categories|length == 0 %}
	<p>{% trans %}ERR_EMPTY_MENUCATEGORY{% endtrans %}</p>
{% else %}
{% if showall == 1 %}
<nav class="category-nav well">
	{% for category in categories %}
	{% if hideempty == 0 or (hideempty == 1 and category.totalproducts > 0) %}
	<a href="{{ category.link }}" title="{{ category.label }}">
		<h1 {% if category.id in path %}class="active"{% endif %}>
			{{ category.label }} {% if showcount == 1 %}({{ category.totalproducts }}){% endif %}
		</h1>
	</a>
	{% if category.children|length > 0  and category.id in path %}
    <ul class="nav nav-pills nav-stacked">
    	{% for subcategory in category.children %}
    	{% if hideempty == 0 or (hideempty == 1 and subcategory.totalproducts > 0) %}
		<li {% if subcategory.id in path %}class="active"{% endif %}><a href="{{ subcategory.link }}">{{ subcategory.label }} {% if showcount == 1 %}({{ subcategory.totalproducts }}){% endif %}</a></li>
		{% endif %}
		{% endfor %}   
    </ul>
    {% endif %}
    {% endif %}
    {% endfor %}   
</nav>
{% endif %}
{% endif %}
{% endblock %}