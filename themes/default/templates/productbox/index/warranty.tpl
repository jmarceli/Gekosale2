<h2>{% trans %}TXT_WARRANTY{% endtrans %}</h2>
<table class="table">
    <tbody>
        {% for warranty in warranty %}
        <tr>
            <td>{{ warranty.name }}</td>
            <th><a href="{{ URL }}redirect/view/{{ warranty.idfile }}">{% trans %}TXT_DOWNLOAD_FILE{% endtrans %}</a></th>
        </tr>
        {% endfor %}
    </tbody>
</table>
