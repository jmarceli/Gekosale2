{% extends "layoutbox.tpl" %}
{% block content %}
<div class="head-block">
	<span class="font">{{ box.heading }}</span>
	{% if CURRENT_CONTROLLER == 'mainside' %}
    <a href="{{ path('frontend.productpromotion') }}" class="pull-right">{% trans %}TXT_SHOW_ALL{% endtrans %} <i class="icon-arrow-right-blue"></i></a>
    {% endif %}
</div>

{% if CURRENT_CONTROLLER == 'productpromotion' %}
<div class="category-options">
  <form class="form-horizontal">
    <fieldset>
      <div class="control-group">
        <label class="control-label" for="sort">{% trans %}TXT_VIEW_LABEL{% endtrans %}:</label>
        <div class="controls">
          <select id="sort" class="input-medium" onchange="location.href=this.value">
            {% for sort in sorting %} 
            <option value="{{ sort.link }}" {% if sort.active %}selected{% endif %}>{{ sort.label }}</option> 
            {% endfor %}
          </select>
        </div>
      </div>
    </fieldset>
  </form>
  <div class="category-view">
    <span>Widok:</span>
    {% for switch in viewSwitcher %} 
    <a href="{{ switch.link }}#sort" class="{% if switch.type == 1 %}list{% else%}box{% endif %} {% if switch.active == 1 %}active{% endif %}"></a>
    {% endfor %}
  </div>
  <div class="clearfix"></div>
</div>
{% endif %}

{% if dataset.rows|length > 0 %}
	{% if pagination == 1 %}
		{% include 'pagination.tpl' %}
	{% endif %}
	{% include 'products.tpl' %}
	{% if pagination == 1 %}
		{% include 'pagination.tpl' %}
	{% endif %}
{% endif %}
{% endblock %}
