{% extends "layout.tpl" %}
{% block stylesheet %}
{{ parent() }}
<link rel="stylesheet" href="{{ DESIGNPATH }}_js_libs/daterangepicker/css/ui.daterangepicker.css?v={{ appVersion }}" type="text/css"/>
<link rel="stylesheet" href="{{ DESIGNPATH }}_js_libs/daterangepicker/css/redmond/jquery-ui-1.7.1.custom.css?v={{ appVersion }}" type="text/css"/>
{% endblock %}	
{% block javascript %}
{{ parent() }}
<script type="text/javascript" src="{{ DESIGNPATH }}_js_libs/daterangepicker/js/daterangepicker.jQuery.js?v={{ appVersion }}"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load('visualization', '1', {'packages':['corechart']});
	google.setOnLoadCallback(function(){
		$('#desktop-simple-stats-sales-chart').GChart({
			fSource:  '{{ URL }}{{ CURRENT_CONTROLLER }}/view/sales'
		});
		
		$('#desktop-simple-stats-orders-chart').GChart({
			fSource:  '{{ URL }}{{ CURRENT_CONTROLLER }}/view/orders'
		});
		
		$('#desktop-simple-stats-customers-chart').GChart({
			fSource:  '{{ URL }}{{ CURRENT_CONTROLLER }}/view/clients'
		});
		
	});

		$(document).ready(function(){
			 $('#period').daterangepicker({
				dateFormat : 'yy/mm/dd',
				posY : '258px',
				onChange: function(){
					location.hash = Base64.encode($('#period').val());
				}, 
			});	
		});
	   
    </script>
{% endblock %}	
{% block content %}
<h2>{% trans %}TXT_DESKTOP{% endtrans %}</h2>
<div class="block" id="desktop">
	<div class="simple-stats layout-two-columns">
		<div class="column narrow">
			<dl class="stats-summary">
				<dt>{% trans %}TXT_SALES{% endtrans %} ({% trans %}TXT_TODAY{% endtrans %} / {% trans %}TXT_CURRENT_MONTH{% endtrans %})</dt><dd>{{ summaryStats.todaysales.total }} {% trans %}TXT_CURRENCY{% endtrans %} / {{ summaryStats.summarysales.total }} {% trans %}TXT_CURRENCY{% endtrans %}</dd>
				<dt>{% trans %}TXT_ORDERS{% endtrans %} ({% trans %}TXT_TODAY{% endtrans %} / {% trans %}TXT_CURRENT_MONTH{% endtrans %})</dt><dd>{{ summaryStats.todaysales.orders }} / {{ summaryStats.summarysales.orders }}</dd>
			    <dt>{% trans %}TXT_CLIENTS{% endtrans %} ({% trans %}TXT_TODAY{% endtrans %} / {% trans %}TXT_CURRENT_MONTH{% endtrans %})</dt><dd>{{ summaryStats.todayclients.totalclients }} / {{ summaryStats.summaryclients.totalclients }} </dd>
			</dl>
		</div>
		<div class="column wide">
			<div class="tabs">
				<ul>
					<li><a href="#desktop-simple-stats-sales">{% trans %}TXT_SALES{% endtrans %}</a></li>
					<li><a href="#desktop-simple-stats-orders">{% trans %}TXT_ORDERS{% endtrans %}</a></li>
					<li><a href="#desktop-simple-stats-customers">{% trans %}TXT_CLIENTS{% endtrans %}</a></li>
				</ul>
			</div>
			<div class="box">
				<div class="field-text" >
					<span class="field" style="width: 150px;margin-top:5px;margin-bottom:5px;">
						<input type="text" id="period" class="period" style="width:142px" value="{{ from }} - {{ to }}" />
					</span>
				</div>
				<div id="desktop-simple-stats-sales">
					<div class="chart" id="desktop-simple-stats-sales-chart"></div>
				</div>
				<div id="desktop-simple-stats-orders">
					<div class="chart" id="desktop-simple-stats-orders-chart"></div>
				</div>
				<div id="desktop-simple-stats-customers">
					<div class="chart" id="desktop-simple-stats-customers-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>
{% endblock %}