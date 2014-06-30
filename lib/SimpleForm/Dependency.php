<?php

/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2009-2011 Gekosale
 *
 * This program is free software; you can redistribute it and/or modify it under the terms 
 * of the GNU General Public License Version 3, 29 June 2007 as published by the Free Software
 * Foundation (http://opensource.org/licenses/gpl-3.0.html).
 * If you did not receive a copy of the license and are unable to obtain it through the 
 * world-wide-web, please send an email to license@verison.pl so we can send you a copy immediately.
 */
namespace SimpleForm;

use Gekosale\App as App;
use xajaxResponse;

class Dependency
{
	const HIDE = 'HIDE';
	const SHOW = 'SHOW';
	const IGNORE = 'IGNORE';
	const SUGGEST = 'SUGGEST';
	const INVOKE_CUSTOM_FUNCTION = 'INVOKE_CUSTOM_FUNCTION';
	const EXCHANGE_OPTIONS = 'EXCHANGE_OPTIONS';
	protected $_id;
	protected $_condition;
	protected $_srcFunction;
	protected $_field;
	protected $_argument;
	protected static $_nextId = 0;

	public function __construct ($type, $field, $condition, $argument = null)
	{
		$this->_argument = $argument;
		$this->type = $type;
		$this->_field = $field;
		$this->_condition = $condition;
	}

	public function GetField ()
	{
		return $this->_field;
	}

	public function GetCondition ()
	{
		return $this->_condition;
	}

	public function Evaluate ($value, $i = null)
	{
		if (! is_subclass_of($this->_condition, 'FormEngine\Condition')){
			return false;
		}
		
		if ($i === null){
			return $this->_condition->Evaluate($this->_field->GetValue());
		}
		$matchingValues = $this->_field->GetValue();
		if (is_array($matchingValues)){
			if (isset($matchingValues[$i])){
				return $this->_condition->Evaluate($matchingValues[$i]);
			}
			else{
				return $this->_condition->Evaluate('');
			}
		}
		return $this->_condition->Evaluate($matchingValues);
	}

	public function doAjaxSuggestionRequest ($request, $responseHandler)
	{
		try{
			$objResponse = new xajaxResponse();
			$response = Array(
				'suggestion' => call_user_func($this->_srcFunction, $request['value'])
			);
			$objResponse->script("{$responseHandler}(" . json_encode($response) . ")");
			return $objResponse;
		}
		catch (Exception $e){
			$objResponse = new xajaxResponse();
			$objResponse->script("GAlert('{_('ERR_PROBLEM_DURING_AJAX_EXECUTION')}', '{$e->getMessage()}');");
			return $objResponse;
		}
	}

	public function doAjaxOptionsRequest ($request, $responseHandler)
	{
		try{
			$objResponse = new xajaxResponse();
			if ($this->_argument !== null){
				$rawOptions = call_user_func($this->_srcFunction, $request['value'], $this->_argument);
			}
			else{
				$rawOptions = call_user_func($this->_srcFunction, $request['value']);
			}
			$options = Array();
			foreach ($rawOptions as $option){
				$value = addslashes($option->value);
				$label = addslashes($option->label);
				$options[] = Array(
					'sValue' => $value,
					'sLabel' => $label
				);
			}
			$response = Array(
				'options' => $options
			);
			$objResponse->script("{$responseHandler}(" . json_encode($response) . ")");
			return $objResponse;
		}
		catch (Exception $e){
			$objResponse = new xajaxResponse();
			$objResponse->script("GAlert('{_('ERR_PROBLEM_DURING_AJAX_EXECUTION')}', '{$e->getMessage()}');");
			return $objResponse;
		}
	}

	public function Render_JS ()
	{
		if (is_subclass_of($this->_condition, 'FormEngine\Condition')){
			return "new GFormDependency(GFormDependency.{$this->type}, '{$this->_field->form->GetName()}.{$this->_field->GetName()}', {$this->_condition->Render_JS()})";
		}
		else{
			switch ($this->type) {
				case self::INVOKE_CUSTOM_FUNCTION:
					if ($this->_argument !== null){
						$argument = json_encode($this->_argument);
						return "new GFormDependency(GFormDependency.{$this->type}, '{$this->_field->form->GetName()}.{$this->_field->GetName()}', {$this->_jsFunction}, {$argument})";
					}
					return "new GFormDependency(GFormDependency.{$this->type}, '{$this->_field->form->GetName()}.{$this->_field->GetName()}', {$this->_jsFunction})";
					break;
				case self::EXCHANGE_OPTIONS:
				case self::SUGGEST:
					return "new GFormDependency(GFormDependency.{$this->type}, '{$this->_field->form->GetName()}.{$this->_field->GetName()}', xajax_{$this->_jsFunction})";
			}
		}
	}

	public function __call ($name, $args)
	{
		if (substr($name, 0, 20) == 'doAjaxOptionsRequest'){
			return call_user_func(Array(
				$this,
				'doAjaxOptionsRequest'
			), $args[0], $args[1]);
		}
		if (substr($name, 0, 23) == 'doAjaxSuggestionRequest'){
			return call_user_func(Array(
				$this,
				'doAjaxSuggestionRequest'
			), $args[0], $args[1]);
		}
	}
}
