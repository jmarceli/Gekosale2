{% extends "layoutbox.tpl" %}
{% block content %}
<div class="tabbable tabs-below">
  <div class="tab-content">
    <div class="tab-pane fade active in" id="A">
      <div id="slideshow-{{ box.id }}" class="carousel slide">
        <div class="carousel-inner">
          {% for slide in slideshow %}
          <div class="item {% if loop.first %}active{% endif %}">
            {% if slide.url %}
            <a href="{{ slide.url }}">
              <img src="{{ DESIGNPATH }}{{ slide.image }}" alt="">
            </a>
            {% else %}
            <img src="{{ DESIGNPATH }}{{ slide.image }}" alt="">
            {% endif %}
            {% if slide.caption %}
            <div class="carousel-caption">
              <h4>{{ slide.caption }}</h4>
            </div>
            {% endif %}
          </div>
          {% endfor %}
        </div>
        {% if slideshow.count > 1 %}
        <a class="left carousel-control" href="#slideshow-{{ box.id }}" data-slide="prev">‹</a>
        <a class="right carousel-control" href="#slideshow-{{ box.id }}" data-slide="next">›</a>
        {% endif %}
      </div>
    </div>
  </div>
</div>
{% endblock %}
