{% extends "layoutbox.tpl" %}
{% block content %}
{% if url != '' %}
	<a href="{{ url }}" style="display:block;width:100%;{{ style }}"></a>
{% endif %}
{% endblock %}