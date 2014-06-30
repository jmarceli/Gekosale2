{% if dataset.total > 0 %}
<ul>
	{section name=links loop=$dataset.totalPages}
		{if $smarty.section.links.first}
			{% dataset.activePage == 1 %}
				<li class="previous disabled"><a href="">{% trans %}TXT_PREVIOUS{% endtrans %}</a></li>
			{% else %}
				<li class="previous"><a href="{{ path('frontend.home') }}{seo controller=$controller seo=$currentCategory.seo page=$dataset.previousPage}">{% trans %}TXT_PREVIOUS{% endtrans %}</a></li>
			{% endif %}
		{% endif %}
			<li class="page {if $dataset.totalPages[links] == $dataset.activePage}active{% endif %}" ><a href="{{ path('frontend.home') }}{seo controller=$controller seo=$currentCategory.seo page=$dataset.totalPages[links]}">{{ dataset.totalPages[links] }}</a></li>
		{if $smarty.section.links.last}
			{% if dataset.activePage == dataset.lastPage %}
				<li class="next disabled"><a href="">{% trans %}TXT_NEXT{% endtrans %}</a></li>
			{% else %}
			<li class="next"><a href="{{ path('frontend.home') }}{seo controller=$controller seo=$currentCategory.seo page=$dataset.nextPage}">{% trans %}TXT_NEXT{% endtrans %}</a></li>
			{% endif %}
		{% endif %}
	{% endfor %}
</ul>
{% endif %}
