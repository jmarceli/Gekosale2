{% extends "layoutbox.tpl" %}
{% block content %}
<article class="article">
{% if not box.settings.bNoHeader and box.heading != '' %}<h1>{{ box.heading }}</h1>{% endif %}
{{ content }}
</article>
{% endblock %}
