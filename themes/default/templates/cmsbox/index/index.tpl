{% extends "layoutbox.tpl" %}
{% block content %}
<article class="article">
  <h1>{{ cms.topic }}</h1>
  {% if cms.undercategorybox is not empty %}
  <ul>
    {% for subpage in cms.undercategorybox %}
    <li><a href="{{ subpage.link }}"><span>{{ subpage.name }}</span></a></li>
    {% endfor %}
  </ul>
  {% endif %}
  {{ cms.content }}
</article>
{% endblock %}
