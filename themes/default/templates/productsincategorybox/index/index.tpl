{% extends "layoutbox.tpl" %}
{% block content %}
<h2 class="category-title">{{ box.heading }}</h2>
{% if currentCategory.description != '' or currentCategory.shortdescription !='' or currentCategory.photo != '' %}
<div class="category-description" {% if currentCategory.photo != '' %}style="background: transparent url('{{ currentCategory.photo }}') no-repeat top right;"{% endif %}>
	<div class="caption">
	{% if currentCategory.description !='' %}
		{{ currentCategory.description }}
	{% else %}
		{{ currentCategory.shortdescription }}
	{% endif %}
	</div>
</div>
{% endif %}
{% if subcategories|length > 0 %}
<div class="row-fluid">
	{% for block in subcategories %}
	<div class="span3 item">
	    <ul class="nav nav-pills nav-stacked">
	    	{% for subcategory in block %}
	        <li><a href="{{ path('frontend.categorylist', {"param": subcategory.seo}) }}" title="{{ subcategory.name }}">{{ subcategory.name }}</a></li>
	        {% endfor %}
		</ul>
    </div>
	{% endfor %}
</div>
{% endif %}
                                    
<div class="category-options">
	<form class="form-horizontal">
    	<fieldset>
        	<div class="control-group">
            	<label class="control-label" for="sort">Sortuj wg:</label>
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
{% include 'pagination.tpl' %} 
{% include 'products.tpl' %}
{% include 'pagination.tpl' %}
{% endblock %}