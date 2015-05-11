{% import "forms.tpl" as forms %} 
{% extends "layoutbox.tpl" %} 
{% block content %}
<h1 class="large">{% trans %}TXT_STEP_1{% endtrans %}</h1>
<div class="row-fluid row-form">
  <div class="span9">
    <div class="span3 alignright">
      <h3 class="normal font20">{% trans %}TXT_ORDER_WITH_ACCOUNT{% endtrans %}</h3>
    </div>
    <div class="span6">
      {% if loginerror is defined %}
      <div class="alert alert-error">
        <strong>{{ loginerror }}</strong>
      </div>
      {% endif %} 
      <form name="{{ formLogin.name }}" id="{{ formLogin.name }}"	method="{{ formLogin.method }}" action="{{ formLogin.action }}">
        <input type="hidden" name="{{ formLogin.submit_name }}" value="1" />
        <fieldset>
          <div class="well well-small">
            <div class="login-form">
              <legend>
                {% trans %}TXT_LOGIN{% endtrans %} <small>{% trans %}TXT_REQUIRED_FIELDS{% endtrans %}</small>
              </legend>
              {{ forms.input(formLogin.children.login, 'input-xlarge') }} 
              {{ forms.password(formLogin.children.password, 'input-xlarge') }}
              {{ forms.hidden(formLogin.children.__csrf) }}
              <div class="form-actions form-actions-clean">
                <a href="{{ path('frontend.forgotpassword') }}" title="{% trans %}TXT_FORGOT_PASSWORD{% endtrans %}">{% trans %}TXT_FORGOT_PASSWORD{% endtrans %}</a>
                <button type="submit" class="btn btn-large btn-primary pull-right">{% trans %}TXT_LOGIN{% endtrans %}</button>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
        </fieldset>
        {{ formLogin.javascript }}
      </form>
    </div>
    <div class="clearfix"></div>
    <div class="span3 alignright">
      <h3 class="normal font20">{% trans %}TXT_SHOPPING_FIRST_TIME{% endtrans %}</h3>
      <h4 class="normal font15">
        {% trans %}TXT_SHOPPING_AS_GUEST{% endtrans %}<br>{% trans %}TXT_OR_LOG_IN{% endtrans %}
      </h4>
    </div>
    <div class="span6">
      <form  name="{{ form.name }}" id="{{ form.name }}" method="{{ form.method }}" action="{{ form.action }}">
        <input type="hidden" name="{{ form.submit_name }}" value="1" />
        <fieldset>
          <div class="well well-small well-clean">
            <legend>
              {% trans %}TXT_PERSON_INFO{% endtrans %} <small>{% trans %}TXT_REQUIRED_FIELDS{% endtrans %}</small>
            </legend>
            {{ forms.radio(form.children.billing_clienttype) }}
            <div class="row-fluid">
              <div class="span6">
                {{ forms.input(form.children.billing_firstname, 'span12') }}
              </div>
              <div class="span6">
                {{ forms.input(form.children.billing_surname, 'span12') }}
              </div>
            </div>
            <div class="row-fluid collapse {% if form.children.billing_clienttype.value == 2 %}in{% endif %}" id="billing-company-data">
              <div class="span6">
                {{ forms.input(form.children.billing_companyname, 'span12') }}
              </div>
              <div class="span6">
                {{ forms.input(form.children.billing_nip, 'span12') }}
              </div>
            </div>
            <div class="row-fluid">
              <div class="span6">
                {{ forms.input(form.children.billing_street, 'span12') }}
              </div>
              <div class="span3">
                {{ forms.input(form.children.billing_streetno, 'span12') }}
              </div>
              <div class="span3">
                {{ forms.input(form.children.billing_placeno, 'span12') }}
              </div>
            </div>
            <div class="row-fluid">
              <div class="span6">
                {{ forms.input(form.children.billing_placename, 'span12') }}
              </div>
              <div class="span3">
                {{ forms.input(form.children.billing_postcode, 'span12') }}
              </div>
            </div>
            <div class="row-fluid">
              <div class="span6">
                {{ forms.select(form.children.billing_country, 'span12') }}
              </div>
            </div>
            <div class="control-group">
              <div class="controls">
                <span class="help-block gray"><small>{% trans %}TXT_OFFERED_INVOICE{% endtrans %}</small></span>
              </div>
            </div>

            {{ forms.checkbox(form.children.other_address, 'span12') }}

            <div id="shipping-data" class="collapse">
              <div class="row-fluid">
                <div class="span6">
                  {{ forms.input(form.children.shipping_firstname, 'span12') }}
                </div>
                <div class="span6">
                  {{ forms.input(form.children.shipping_surname, 'span12') }}
                </div>
              </div>
              <div class="row-fluid">
                <div class="span6">
                  {{ forms.input(form.children.shipping_companyname, 'span12') }}
                </div>
              </div>
              <div class="row-fluid">
                <div class="span6">
                  {{ forms.input(form.children.shipping_street, 'span12') }}
                </div>
                <div class="span3">
                  {{ forms.input(form.children.shipping_streetno, 'span12') }}
                </div>
                <div class="span3">
                  {{ forms.input(form.children.shipping_placeno, 'span12') }}
                </div>
              </div>
              <div class="row-fluid">
                <div class="span6">
                  {{ forms.input(form.children.shipping_placename, 'span12') }}
                </div>
                <div class="span3">
                  {{ forms.input(form.children.shipping_postcode, 'span12') }}
                </div>
              </div>
              <div class="row-fluid">
                <div class="span6">
                  {{ forms.select(form.children.shipping_country, 'span12') }}
                </div>
              </div>
            </div>

            <legend class="marginbt10">{% trans %}TXT_CONTACT_BOX{% endtrans %}</legend>
            <div class="row-fluid">
              <div class="span6">
                {{ forms.input(form.children.phone, 'span12') }}
              </div>
            </div>
            <div class="row-fluid">
              <div class="span6">
                {{ forms.input(form.children.phone2, 'span12') }}
              </div>
            </div>
            <div class="row-fluid marginbt20">
              <div class="span6">
                {{ forms.input(form.children.email, 'span12') }}
              </div>
            </div>
          </div>
          <div class="well well-small">
            {{ forms.checkbox(form.children.create_account, 'span12') }}
            <div class="collapse {% if(form.children.create_account.value == 1) %}in{% endif %}" id="create-account">
              <legend class="marginbt10">{% trans %}TXT_ACCOUNT_INFO{% endtrans %}</legend>
              <div>
                <div class="password-form">
                  {{ forms.password(form.children.password, 'span12') }}
                  {{ forms.password(form.children.confirmpassword, 'span12') }}
                </div>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <div class="well well-small">
            <legend>{% trans %}TXT_CONDITIONS_AND_NEWSLETTER{% endtrans %}</legend>
            {{ forms.checkbox(form.children.confirmterms) }}
            {{ forms.checkbox(form.children.newsletter) }}
          </div>
        </fieldset>
        <fieldset>
          {{ forms.hidden(form.children.__csrf) }}
          <div class="form-actions form-actions-clean pull-right">
            <a href="{{ path('frontend.cart') }}" title=""><i class="icon icon-arrow-left-blue"></i> {% trans %}TXT_BACK_TO_SHOPPING{% endtrans %}</a>
            <button type="submit" class="btn btn-large btn-primary marginlt20">{% trans %}TXT_CONFIRM_ORDER_DATA{% endtrans %} <i class="icon icon-arrow-right icon-white"></i></button>
          </div>
        </fieldset>
        {{ form.javascript }}
      </form>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(document).ready(function(){
      {% if form.children.billing_clienttype.value != 2 %}
      $('#billing-company-data').find('input').attr('tabindex', -1);
      {% endif %}

      $("#{{ form.name }} input[name='billing_clienttype']").unbind('change').bind('change', function(){
        $('#billing-company-data').collapse($(this).val() == 2 ? 'show' : 'hide');
        if($(this).val() == 2){
        $('#billing-company-data').find('input').removeAttr('tabindex');
        }else{
        $('#billing-company-data').find('input').attr('tabindex', -1);
        }
        });
      $("#{{ form.name }} input[name='other_address']").unbind('change').bind('change', function(){
        $('#shipping-data').collapse($(this).is(':checked') ? 'show' : 'hide');
        });

      $("#{{ form.name }} input[name='create_account']").unbind('change').bind('change', function(){
        $('#create-account').collapse($(this).is(':checked') ? 'show' : 'hide');
        });
      });
    </script>
    {% endblock %}
