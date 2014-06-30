{% for s in summary %}
<tr class="special">
	<td colspan="4" class="alignright"><h4>{{ s.label }}</h4></td>
	<td colspan="2" class="center"><h3>{{ s.value }}</h3></td>
</tr>
{% endif %}