<h1 style="margin-top:0; font-size:18px; color:#231f20; border-bottom:1px solid #e3e3e3; padding-bottom:6px; text-transform:uppercase;">{% trans %}TXT_ACCEPT_AN_ORDER{% endtrans %}</h1>
<p>{% trans %}TXT_EMAIL_HELLO{% endtrans %},<br/>

{% trans %}TXT_THANKS_FOR_ORDER{% endtrans %} <a target="_blank" href="{{ path('frontend.home') }}" title="{{ SHOP_NAME }}" style="color:#f15a25; text-decoration:none;">{{ SHOP_NAME }}</a>.</p>
<p>{% trans %}TXT_ON_THE_DAY{% endtrans %} {{ "now"|date("d/m/Y") }} {% trans %}TXT_WE_RECEIVED_AN_ORDER{% endtrans %} <strong style="color:#231f20;">{{ orderId }}</strong></p>

<p style="margin-top:20px;"><strong style="color:#231f20;">{% trans %}TXT_ORDER_DETAILS{% endtrans %}:</strong></p>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<thead>
		<tr style="color:#787372; background:#ebebeb; font-size:11px; line-height:26px; text-align:left;">
			<th style="border-bottom:1px solid #e1e1e1; border-right:1px solid #fff; padding-left:10px; font-weight:normal;">{% trans %}TXT_PRODUCT_NAME{% endtrans %}</th>
			<th style="border-bottom:1px solid #e1e1e1; border-right:1px solid #fff; padding-left:10px; font-weight:normal;">{% trans %}TXT_EAN{% endtrans %}</th>
			<th style="border-bottom:1px solid #e1e1e1; border-right:1px solid #fff; padding-left:10px; font-weight:normal; width:60px">{% trans %}TXT_PRODUCT_PRICE{% endtrans %}</th>
			<th style="border-bottom:1px solid #e1e1e1; border-right:1px solid #fff; padding-left:10px; font-weight:normal;">{% trans %}TXT_QUANTITY{% endtrans %}</th>
			<th style="border-bottom:1px solid #e1e1e1; border-right:1px solid #fff; padding-left:10px; font-weight:normal; width:85px">{% trans %}TXT_VALUE{% endtrans %}</th>
		</tr>
	</thead>
	<tbody style="color:#231f20;">
	{% for product in order.cart %} 
		{% if product.standard is defined %}
		<tr style="font-size:11px; line-height:26px; text-align:left;">
			<td>{{ product.name }}</td>
			<td>{{ product.ean }}</td>
			<td>{{ product.newprice|priceFormat }}</td>
			<td>{{ product.qty }} {% trans %}TXT_QTY{% endtrans %}</td>
			<td>{{ product.qtyprice|priceFormat }}</td>
		</tr>
		{% endif %}
		{% for attributes in product.attributes %}
		<tr style="font-size:11px; line-height:26px; text-align:left;">
			<td>{{ attributes.name }}<br />
			{% for features in attributes.features %} <small>
			{{ features.group }}: {{ features.attributename }}&nbsp;&nbsp;</small> {% endfor %}</td>
			<td>{{ attributes.ean }}</td>
			<td>{{ attributes.newprice|priceFormat }}</td>
			<td>{{ attributes.qty }} {% trans %}TXT_QTY{% endtrans %}</td>
			<td>{{ attributes.qtyprice|priceFormat }}</td>
		</tr>
		{% endfor %} 
	{% endfor %}
	</tbody>
	<tfoot style="color:#231f20;">
		<tr style="background:#e2e2e2; line-height:30px;">
			<td colspan="4" style="background:#e2e2e2; color:#231f20; font-weight:bold; font-size:11px; padding-right:10px; text-align:right;">{% trans %}TXT_SUM{% endtrans %}</td>
			<td style="background:#e2e2e2; color:#231f20; font-weight:bold; font-size:11px; padding-left:10px;">{{ order.globalPrice|priceFormat }}</td>
		</tr>
		<tr style="line-height:30px;">
			<td colspan="4" style="background:#e2e2e2; color:#231f20; font-weight:bold; font-size:11px; padding-right:10px; text-align:right;">{{ order.dispatchmethod.dispatchmethodname }}</td>
			<td style="background:#e2e2e2; color:#231f20; font-weight:bold; font-size:11px; padding-left:10px;">{{ order.dispatchmethod.dispatchmethodcost|priceFormat }}</td>
		</tr>
		{% if order.rulescart is defined %}
		<tr style="line-height:30px;">
			<td colspan="4" style="background:#e2e2e2; color:#231f20; font-weight:bold; font-size:11px; padding-right:10px; text-align:right;">{{ order.rulescart }}</td>
			<td style="background:#e2e2e2; color:#231f20; font-weight:bold; font-size:11px; padding-left:10px;">{{ order.rulescartmessage }}</td>
		</tr>
		<tr style="line-height:30px;">
			<td colspan="4" style="background:#e2e2e2; color:#231f20; font-weight:bold; font-size:11px; padding-right:10px; text-align:right;">{% trans %}TXT_VIEW_ORDER_TOTAL{% endtrans %}</td>
			<td style="background:#e2e2e2; color:#231f20; font-weight:bold; font-size:11px; padding-left:10px;">{{ order.priceWithDispatchMethodPromo|priceFormat }}</td>
		</tr>
		{% else %}
		<tr style="line-height:30px;">
			<td colspan="4" style="background:#e2e2e2; color:#231f20; font-weight:bold; font-size:11px; padding-right:10px; text-align:right;">{% trans %}TXT_VIEW_ORDER_TOTAL{% endtrans %}</td>
			<td style="background:#e2e2e2; color:#231f20; font-weight:bold; font-size:11px; padding-left:10px;">{{ order.priceWithDispatchMethod|priceFormat }}</td>
		</tr>
		{% endif %}
	</tfoot>
</table>

<h2 style="font-size:14px; color:#231f20; border-bottom:1px solid #e3e3e3; padding-bottom:4px; margin-top:30px; text-transform:uppercase;">{% trans %}TXT_METHOD_OF_PEYMENT{% endtrans %}</h2>
<p>{{ order.payment.paymentmethodname }}<p>
{% if paymentmodel == 'banktransfer' %}
{% if bankdata.bankacct %}
<p><b>{% trans %}TXT_BANK_NUMBER{% endtrans %}</b>: {{ bankdata.bankacct }}</p>
{% endif %}
<p><b>{% trans %}TXT_BANK_NAME{% endtrans %}</b> {{ bankdata.bankname }}</p>
<p><b>{% trans %}TXT_BANK_TRANSFER_TITLE{% endtrans %}:</b> {% trans %}TXT_ORDER{% endtrans %} {{ orderId }}</p>
{% endif %}

<h2 style="font-size:14px; color:#231f20; border-bottom:1px solid #e3e3e3; padding-bottom:4px; margin-top:30px; text-transform:uppercase;">Dostawa</h2>
<p>{{ order.dispatchmethod.dispatchmethodname }}</p>

<h2 style="font-size:14px; color:#231f20; border-bottom:1px solid #e3e3e3; padding-bottom:4px; margin-top:30px; text-transform:uppercase;">{% trans %}TXT_CLIENT{% endtrans %}:</h2>
<p>
{% if order.clientaddress.companyname !='' %}
<br>{% trans %}TXT_COMPANYNAME{% endtrans %} : {{ order.clientaddress.companyname }}
{% endif %} 
{% if order.clientaddress.nip != '' %}
<br>{% trans %}TXT_NIP{% endtrans %}: {{ order.clientaddress.nip }}
{% endif %} 
<br>{% trans %}TXT_FIRSTNAME{% endtrans %}:	{{ order.clientaddress.firstname }} 
<br>{% trans %}TXT_SURNAME{% endtrans %}: {{ order.clientaddress.surname }} 
<br>{% trans %}TXT_PLACENAME{% endtrans %}: {{ order.clientaddress.placename }}
<br>{% trans %}TXT_POSTCODE{% endtrans %}: {{ order.clientaddress.postcode }}
<br>{% trans %}TXT_STREET{% endtrans %}: {{ order.clientaddress.street }} 
<br>{% trans %}TXT_STREETNO{% endtrans %}: {{ order.clientaddress.streetno }}
{% if order.clientaddress.placeno %}
<br>{% trans %}TXT_PLACENO{% endtrans %}: {{ order.clientaddress.placeno }}
{% endif %}
<br>{% trans %}TXT_PHONE{% endtrans %}: {{ order.contactData.phone }}
<br>
</p>

<h2 style="font-size:14px; color:#231f20; border-bottom:1px solid #e3e3e3; padding-bottom:4px; margin-top:30px; text-transform:uppercase;">{% trans %}TXT_DELIVERER_ADDRESS{% endtrans %}:</h2>
<p>
<br>{% trans %}TXT_FIRSTNAME{% endtrans %}: {{ order.deliveryAddress.firstname }} 
<br>{% trans %}TXT_SURNAME{% endtrans %}: {{ order.deliveryAddress.surname }} 
<br>{% trans %}TXT_PLACENAME{% endtrans %}: {{ order.deliveryAddress.placename }}
<br>{% trans %}TXT_POSTCODE{% endtrans %}: {{ order.deliveryAddress.postcode }}
<br>{% trans %}TXT_STREET{% endtrans %}: {{ order.deliveryAddress.street }} 
<br>{% trans %}TXT_STREETNO{% endtrans %}: {{ order.deliveryAddress.streetno }}
{% if order.deliveryaddress.placeno %}
<br>{% trans %}TXT_PLACENO{% endtrans %}: {{ order.deliveryAddress.placeno }}
{% endif %}
<br>{% trans %}TXT_PHONE{% endtrans %}: {{ order.contactData.phone }}
</p>

{% if order.customeropinion != '' %}
<h2 style="font-size:14px; color:#231f20; border-bottom:1px solid #e3e3e3; padding-bottom:4px; margin-top:30px; text-transform:uppercase;">{% trans %}TXT_PRODUCT_REVIEW{% endtrans %}:</h2>
<p>{{ order.customeropinion }}</p>
{% endif %}
