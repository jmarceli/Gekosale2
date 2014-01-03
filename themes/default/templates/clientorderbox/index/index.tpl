{% extends "layoutbox.tpl" %}
{% block content %}
{% if orderlist|length > 0 %}
<article class="article">
	<h1>{% trans %}TXT_CLIENT_ORDER_HISTORY{% endtrans %}</h1>
    <table class="table table-striped table-bordered history-order">
    	<thead>
        	<tr class="thead-info">
            	<td colspan="6">{% trans %}TXT_YOUR_ORDERS_SORTED_DATE{% endtrans %}</td>
            </tr>
            <tr>
            	<th>{% trans %}TXT_ORDER{% endtrans %}</th>
                <th>{% trans %}TXT_DATE{% endtrans %}</th>
                <th>{% trans %}TXT_SUM{% endtrans %}</th>
                <th>{% trans %}TXT_PAYMENT{% endtrans %}</th>
                <th>{% trans %}TXT_STATUS{% endtrans %}</th>
                <th>{% trans %}TXT_OPTIONS{% endtrans %}</th>
			</tr>
		</thead>
        <tbody>
        	{% for other in orderlist %}
        	<tr>
            	<td>{{ other.idorder }}</td>
                <td>{{ other.orderdate }}</td>
                <td><strong>{{ other.globalprice }} {{ other.currencysymbol }}</strong></td>
                <td>{{ other.paymentmethodname }}</td>
                <td style="color: #{{ other.colour }};">{{ other.orderstatusname }}</td>
                <td><a href="{{ path('frontend.clientorder', {"param": other.idorder}) }}" title="">{% trans %}TXT_SHOW{% endtrans %}</a></td>
			</tr>
			{% endfor %}
		</tbody>
	</table>                        
</article>
{% else %}
<div class="alert alert-block alert-info">
	{% trans %}TXT_NOT_ORDERS_NOT_WAIT{% endtrans %}
</div>
{% endif %}
{% endblock %}
