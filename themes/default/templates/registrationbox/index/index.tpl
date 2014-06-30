{% import "forms.tpl" as forms %}
{% extends "layoutbox.tpl" %} 
{% block content %}
<div class="order-wrap" style="background: none;">
	<div class="row-fluid row-form">
		<div class="span9"> 
			<div class="span3">
				<h3 class="normal font20">Nie posiadam konta</h3>
			</div>
			<div class="span6">
				<form name="{{ form.name }}" id="{{ form.name }}" method="{{ form.method }}" action="{{ form.action }}">
					<input type="hidden" name="{{ form.submit_name }}" value="1" />
					<fieldset>
						<div class="well well-small well-clean">
							<legend>
								Zarejestruj się <small>*Pola wymagane</small>
							</legend>
							
							{{ forms.input(form.children.firstname, 'input-large') }}
							
							{{ forms.input(form.children.surname, 'input-large') }}
							
							{{ forms.input(form.children.phone, 'input-large') }}
							
							{{ forms.input(form.children.email, 'input-large') }}
							
							{{ forms.password(form.children.password, 'input-large') }}
							
							{{ forms.password(form.children.confirmpassword, 'input-large') }}
							
						</div>
					</fieldset>
					<fieldset>
						<div class="well well-small">
							<legend>Warunki sklepu oraz biuletyn okazji</legend>
							{{ forms.checkbox(form.children.confirmterms) }}
							{{ forms.checkbox(form.children.newsletter) }}
						</div>
					</fieldset>
					<fieldset>
						{{ forms.hidden(form.children.__csrf) }}
						<div class="form-actions form-actions-clean">
							<button type="submit" class="btn btn-large btn-primary pull-right">Zarejestruj się</button>
						</div>
					</fieldset>
					{{ form.javascript }}
				</form>
			</div>
		</div>
	</div>
</div>
{% endblock %}
