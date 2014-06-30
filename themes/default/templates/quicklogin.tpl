<form action="" id="quickLogin" name="quickLogin">
	<div class="control-group error">
		<span class="error help-block" id="login-error"></span>
	</div>
	<div class="control-group">
		<label class="control-label" for="input01">Adres e-mail</label>
		<div class="controls">
			<div class="input-prepend">
				<span class="add-on"><i class="icon-envelope"></i></span><input	type="text" class="input-large" id="quick-login" name="quick-login">
			</div>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="input02">Hasło</label>
		<div class="controls">
			<div class="input-prepend">
				<span class="add-on"><i class="icon-lock"></i></span><input	type="password" class="input-large" id="quick-password" name="quick-password">
			</div>
		</div>
	</div>
	<div class="control-group nomargin">
		<input type="checkbox" class="input-large" id="quick-autologin" name="quick-autologin" /> <label class="control-label" for="quick-autologin" style="float:left; padding-right:10px;">{% trans %}TXT_AUTOLOGIN{% endtrans %}</label><br />
		<a href="{{ path('frontend.forgotpassword') }}">Przypomnij hasło</a>
		<button type="submit" class="btn btn-primary pull-right">Zaloguj się</button>
	</div>
</form>
<script>
$(document).ready(function(){
	$('#quickLogin').validate({
		errorElement: 'span',
		errorClass: 'error',
		wrapClass: 'help-block',
		rules: {
			"quick-login":{
				"required":true,
				"email":true
			},
			"quick-password":{
				"required":true
			}
		},
		messages: {
			"quick-login":{
				"required":"{% trans %}ERR_EMPTY_EMAIL{% endtrans %}",
				"email": "{% trans %}ERR_WRONG_EMAIL{% endtrans %}"
			},
			"quick-password":{
				"required":"{% trans %}ERR_EMPTY_PASSWORD{% endtrans %}"
			}
		},
		highlight: function(label) {
			$(label).addClass('invalid').closest('.control-group').addClass('error');
		},
		success: function(label) {
			label.addClass('valid').closest('.control-group').addClass('success');
		},
		submitHandler: function(form) {
			xajax_doQuickLogin($('#quick-login').val(), $('#quick-password').val(), $('#quick-autologin').val());
		}
	});
});
</script>