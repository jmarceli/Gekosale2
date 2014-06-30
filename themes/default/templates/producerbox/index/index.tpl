{% extends "layoutbox.tpl" %}
{% block content %}
{% if CURRENT_PARAM != '' and CURRENT_CONTROLLER == 'producerlist' %}
<nav class="category-nav well">
	{% for producer in producers %}
	<a href="{{ producer.link }}" title="{{ producer.name }}">
		<h1 {% if producer.active %}class="active"{% endif %}>
			{{ producer.name }}
		</h1>
	</a>
    {% endfor %}   
</nav>
{% else %}
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
{% endif %}
{% endblock %}