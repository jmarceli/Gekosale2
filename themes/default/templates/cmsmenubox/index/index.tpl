{% extends "layoutbox.tpl" %}
{% block content %}
<nav class="category-nav well">
	{% for cat in contentcategory %}
	<a href="{{ cat.link }}" title="{{ cat.name }}">
		<h1 {% if cat.id in activePath %}class="active"{% endif %}>
			{{ cat.name }}
		</h1>
	</a>
	{% if cat.children is not empty and cat.id in activePath %}
    <ul class="nav nav-pills nav-stacked">
    	{% for subcat in cat.children if subcat.header == 1 %}
		<li {% if subcat.id in activePath %}class="active"{% endif %}><a href="{{ subcat.link }}">{{ subcat.name }}</a></li>
		{% endfor %}   
    </ul>
    {% endif %}
    {% endfor %}   
</nav>
{% endblock %}