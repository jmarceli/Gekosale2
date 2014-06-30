<p>Nadawca: <strong style="color:#231f20;">{{ firstname }} {{ surname }}</strong></p>
<p>Adres e-mail: <a href="mailto:{{ phone }}">{{ email }}</a></p>
<p>Telefon: {{ phone }}</p>
<p>Wiadomość:<br /><br />{{ CONTACT_CONTENT }}</p>
{% if productLink is defined %}
<p><a href="{{ productLink }}">Zobacz produkt w sklepie</a></p>
{% endif %}