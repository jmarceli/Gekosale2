{% include 'header.tpl' %}
<section id="content" class="content layout-boxes">
	<div id="searchNoResults">
		<span class="ico"><img src="{{ ASSETSPATH }}img/search.ico.png" alt=""></span>
		<h1>
      <strong>{% trans %}TXT_INVALID_URL{% endtrans %}</strong>
		</h1>
		<h2>
			{% trans %}TXT_PAGE_NOT_EXISTS{% endtrans %}
		</h2>
	</div>
	<div class="row">
		<div class="span12">
			<article class="article marginbt50">

				<h1 class="noborder">{% trans %}TXT_WHAT_TO_DO{% endtrans %}</h1>
				<p>
					{% trans %}TXT_GO_TO{% endtrans %} <a href="{{ path('frontend.home') }}">{% trans %}TXT_SERVICE_MAIN_PAGE{% endtrans %}</a><br>{% trans %}TXT_CALL_OR_WRITE_ERROR{% endtrans %}
				</p>

				<div class="row-fluid">
					<div class="span3 nomargin">
            <a href="#" title="" class="email">{{ defaultcontact.email }}</a>
					</div>
					<div class="pull-left phone nomargin">
            <h3 class="font">{{ defaultcontact.phone }}</h3>
            <span>{% trans %}TXT_WORKING_DAYS{% endtrans %} {{ defaultcontact.businesshours }}</span>
					</div>
				</div>

			</article>
			
			{% include 'products.tpl' %}
		</div>
	</div>
</section>
{% include 'footer.tpl' %}
