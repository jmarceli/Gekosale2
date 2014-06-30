{% include 'header.tpl' %}
<section id="content" class="content layout-boxes">
	<div id="searchNoResults">
		<span class="ico"><img src="{{ ASSETSPATH }}img/search.ico.png" alt=""></span>
		<h1>
			Niepoprawny adres</strong>
		</h1>
		<h2>
			Taka strona nie istnieje
		</h2>
	</div>
	<div class="row">
		<div class="span12">
			<article class="article marginbt50">

				<h1 class="noborder">Nie wiesz co zrobić?</h1>
				<p>
					Przejdź na <a href="{{ path('frontend.home') }}">stronę główną serwisu.</a><br>W przypadku problemów możesz również zadzwonić lub napisać.
				</p>

				<div class="row-fluid">
					<div class="span3 nomargin">
						<a href="#" title="" class="email">info@sklep.pl</a>
					</div>
					<div class="pull-left phone nomargin">
						<h3 class="font">800 887 999</h3>
						<span>pn-pt 7:00 - 16:00</span>
					</div>
				</div>

			</article>
			
			{% include 'products.tpl' %}
		</div>
	</div>
</section>
{% include 'footer.tpl' %}
