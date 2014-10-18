{% extends "layoutbox.tpl" %}
{% block content %}
<nav class="category-nav well">
	{% for producer in producers %}
	<a href="{{ producer.link }}" title="{{ producer.name }}">
		<h1 {% if producer.active %}class="active"{% endif %}>
			{{ producer.name }}
		</h1>
	</a>
  {% endfor %}   
</nav>
{% endblock %}
