<?php

/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2008-2013 WellCommerce sp. z o.o.. Zabronione jest usuwanie informacji o licencji i autorach.
 *
 * This library is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version. 
 * 
 */
namespace Gekosale;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Loader_String;
use Twig_Filter_Function;
use Twig_Function_Function;
use Twig_Extensions_Extension_I18n;
use Twig_Extension_Optimizer;
use Twig_NodeVisitor_Optimizer;
use FormEngine;
use mysqli;
use Exception;
use PasswordHash;

class Install
{
	protected $tplFile = 'installer.tpl';

	public function __construct ()
	{
		$designPaths = Array();
		$designPaths[] = ROOTPATH . 'design' . DS;
		$this->error = NULL;
		
		$this->template = new Twig_Environment(new Twig_Loader_Filesystem($designPaths), array(
			'cache' => ROOTPATH . 'cache' . DS,
			'auto_reload' => true,
			'autoescape' => false
		));
	}

	protected function check_dir ($path)
	{
		if (! is_dir($path)){
			mkdir($path, 0770);
		}
		return true;
	}

	protected function panelURL ($panel)
	{
		$file = 'index.php/' . $panel;
		if (LOCAL_CATALOG != ''){
			return App::getHost(1) . '/' . LOCAL_CATALOG . '/' . $file;
		}
		return App::getHost(1) . '/' . $file;
	}

	protected function configWriter ($Config)
	{
		$filename = ROOTPATH . 'config' . DS . 'settings.php';
		$out = fopen($filename, "w");
		fwrite($out, "<?php defined('ROOTPATH') OR die('No direct access allowed.');\r\n");
		fwrite($out, '/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2008-2012 Gekosale. Zabronione jest usuwanie informacji o licencji i autorach.
 *
 * This library is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version. 
 * 
 */' . "\r\n");
		
		fwrite($out, '
		$Settings = Array(' . "
			'database' => Array(
				'driver' => 'pdo_mysql',
				'host' => '{$Config['hostspec']}',
				'user' => '{$Config['username']}',
				'password' => '{$Config['password']}',
				'dbname' => '{$Config['database']}'
			),
			'admin_panel_link' => 'admin',
			'client_data_encription_string' => '{$Config['client_data_encription_string']}',
			'ssl' => 0,
		);");
		fclose($out);
	}

	protected function checkExtension ($ext)
	{
		if (extension_loaded($ext)){
			return true;
		}
		if (ini_get('enable_dl') !== 1 || ini_get('safe_mode') === 1){
			return false;
		}
		$file = (PHP_SHLIB_SUFFIX === 'dll' ? 'php_' : '') . $ext . '.' . PHP_SHLIB_SUFFIX;
		return true;
	}

	protected function checkPhpVersion ($version)
	{
		if (version_compare(phpversion(), $version, '<=') === true){
			return false;
		}
		else{
			return true;
		}
	}

	public function testDBConnection ($data)
	{
		$db = @(new mysqli($data['hostspec'], $data['username'], $data['password'], $data['database'], 3306));
		if (mysqli_connect_error()){
			return mysqli_connect_error();
		}
		return true;
	}

	public function importDatabase ($data)
	{
		$data['namespace'] = 'gekosale';
		
		@set_time_limit(0);
		$db = @(new mysqli($data['hostspec'], $data['username'], $data['password'], $data['database'], 3306));
		try{
			$db->query('SET names utf8');
			$db->query('SET FOREIGN_KEY_CHECKS = 0');
			$db->autocommit(false);
      $files = scandir(ROOTPATH . 'sql/');
      foreach ($files as $fh) {
				if (strpos($fh, '.sql') !== FALSE){
					$file = file_get_contents(ROOTPATH . 'sql/' . $fh);
					$sql = explode(";\n", $file);
					foreach ($sql as $query){
						if (strlen($query) > 5){
							if (! @$db->query($query)){
								throw new Exception($db->error . "\r\nquery:" . $query);
							}
						}
					}
				}
			}
			
			$sql = "UPDATE user SET login=SHA1(?),password=SHA1(?), active=1 WHERE iduser = 1";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ss', $data['user_email'], $data['user_password']);
			$stmt->execute();
			
			$sql2 = "UPDATE userdata SET email = ? WHERE userid = 1";
			$stmt2 = $db->prepare($sql2);
			$stmt2->bind_param('s', $data['user_email']);
			$stmt2->execute();
			
			$sql5 = 'UPDATE viewurl SET url = ? WHERE viewid = 3';
			$stmt5 = $db->prepare($sql5);
			$stmt5->bind_param('s', $_SERVER['HTTP_HOST']);
			$stmt5->execute();
			
			$db->commit();
			$db->autocommit(true);
			$db->query('SET FOREIGN_KEY_CHECKS = 1');
			$db->close();
		}
		catch (Exception $e){
			$db->rollback();
			throw $e;
		}
	}

	public function Render ()
	{
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'install',
			'action' => '',
			'method' => 'post',
			'csrf' => false
		));
		
		$checkDirLogs = $this->check_dir('logs');
		$checkDirCache = $this->check_dir('cache');
		$checkDirSerialization = $this->check_dir('serialization');
		
		$minPHP = '5.3';
		
		$checkPHPVersion = $this->checkPhpVersion($minPHP);
		$checkExtensionZlib = $this->checkExtension('zlib');
		$checkExtensionGd = $this->checkExtension('gd');
		$checkExtensionMysqli = $this->checkExtension('mysqli');
		$checkExtensionCurl = $this->checkExtension('curl');
		
		//if(ini_get('output_buffering')){
      $checkOB = true;
		//}else{
			//$checkOB = false;
		//}
		
		if ($checkOB == TRUE && $checkPHPVersion == TRUE && $checkExtensionZlib == TRUE && $checkExtensionMysqli == TRUE && $checkExtensionCurl == TRUE && $checkExtensionGd = TRUE){
			
			$license = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
				'name' => 'license',
				'label' => 'Licencja'
			)));
			
			$licenceFile = file_get_contents(ROOTPATH . 'LICENCE');
			
			$gpl = '<div style="height:20em; border:1px solid #ccc; margin-bottom:8px; padding:5px; background:#fff; overflow: auto; overflow-x:hidden; overflow-y:scroll;">
				' . $licenceFile . '
				</div>';
			
			$license->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => $gpl
			)));
			
			$license->AddChild(new FormEngine\Elements\Checkbox(Array(
				'name' => 'accept_license',
				'label' => 'Akceptuję licencję',
				'rules' => Array(
					new FormEngine\Rules\Required('Musisz zaakceptować licencję.')
				),
				'default' => 0
			)));
			
			$store = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
				'name' => 'store',
				'label' => 'Dane administratora sklepu'
			)));
			
			$store->AddChild(new FormEngine\Elements\TextField(Array(
				'name' => 'user_email',
				'comment' => 'Używany jako login',
				'label' => 'E-mail',
				'rules' => Array(
					new FormEngine\Rules\Required('Podaj login.')
				)
			)));
			
			$store->AddChild(new FormEngine\Elements\Password(Array(
				'name' => 'user_password',
				'label' => 'Hasło',
				'rules' => Array(
					new FormEngine\Rules\Required('Podaj hasło.')
				)
			)));
			
			$database = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
				'name' => 'database_settings',
				'label' => 'Ustawienia bazy SQL'
			)));
			
			$database->AddChild(new FormEngine\Elements\TextField(Array(
				'name' => 'hostspec',
				'label' => 'Host',
				'default' => 'localhost',
				'rules' => Array(
					new FormEngine\Rules\Required('Podaj host bazy SQL.')
				)
			)));
			
			$database->AddChild(new FormEngine\Elements\TextField(Array(
				'name' => 'mysqlport',
				'label' => 'Port',
				'default' => 3306,
				'rules' => Array(
					new FormEngine\Rules\Required('Podaj port bazy SQL.')
				)
			)));
			
			$database->AddChild(new FormEngine\Elements\TextField(Array(
				'name' => 'username',
				'label' => 'Użytkownik',
				'rules' => Array(
					new FormEngine\Rules\Required('Podaj użytkownika bazy SQL.')
				)
			)));
			
			$database->AddChild(new FormEngine\Elements\Password(Array(
				'name' => 'password',
				'label' => 'Hasło',
			)));
			
			$database->AddChild(new FormEngine\Elements\TextField(Array(
				'name' => 'database',
				'label' => 'Nazwa bazy',
				'rules' => Array(
					new FormEngine\Rules\Required('Podaj nazwę bazy SQL.')
				)
			)));
			
			$database->AddChild(new FormEngine\Elements\TextField(Array(
				'name' => 'client_data_encription_string',
				'label' => 'Klucz szyfrujący dane',
				'default' => substr(md5(uniqid(rand(), true)), 0, 32),
				'rules' => Array(
					new FormEngine\Rules\Required('Musisz podać klucz szyfrujący.')
				)
			)));
			
			$installation = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
				'name' => 'installation',
				'label' => 'Instalacja'
			)));
			
			$installation->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => '<a href="#" class="installButton">Zainstaluj Gekosale</a>'
			)));
			
			$form->AddFilter(new FormEngine\Filters\Trim());
			
			if ($form->Validate(FormEngine\FE::SubmittedData())){
				$Data = $form->getSubmitValues(FormEngine\Elements\Form::FORMAT_FLAT);
				$conn = $this->testDBConnection($Data);
				if ($conn === TRUE){
					$this->importDatabase($Data);
					$this->configWriter($Data);
					header('Location: ' . $this->panelURL('admin'));
				}
				else{
					$this->error = addslashes($conn);
				}
			}
		}
		else{
			$requirements = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
				'name' => 'requirements',
				'label' => 'Requirements'
			)));
			
			$requirements->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => (($checkPHPVersion == TRUE) ? '<p style="color: green;"><strong>PHP version</strong> - OK</p>' : '<p style="color: red"><strong>PHP version</strong> - ' . $minPHP . ' required, ' . phpversion() . ' available</p>')
			)));
			
			$requirements->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => (($checkExtensionZlib == TRUE) ? '<p style="color: green;"><strong>Zlib extension</strong> - OK</p>' : '<p style="color: red"><strong>Zlib extension</strong> - No</p>')
			)));
			
			$requirements->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => (($checkExtensionMysqli == TRUE) ? '<p style="color: green;"><strong>Mysqli extension</strong> - OK</p>' : '<p style="color: red"><strong>Mysqli extension</strong> - No</p>')
			)));
			
			$requirements->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => (($checkExtensionCurl == TRUE) ? '<p style="color: green;"><strong>cURL extension</strong> - OK</p>' : '<p style="color: red"><strong>cURL extension</strong> - No</p>')
			)));
			
			$requirements->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => (($checkExtensionGd == TRUE) ? '<p style="color: green;"><strong>GD extension</strong> - OK</p>' : '<p style="color: red"><strong>GD extension</strong> - No</p>')
			)));
			
			$requirements->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => (($checkDirLogs == TRUE) ? '<p style="color: green;"><strong>"logs" dir writeable</strong> - OK</p>' : '<p style="color: red"><strong>"logs" dir writeable</strong> - No</p>')
			)));
			
			$requirements->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => (($checkDirCache == TRUE) ? '<p style="color: green;"><strong>"cache" dir writeable</strong> - OK</p>' : '<p style="color: red"><strong>"cache" dir writeable</strong> - No</p>')
			)));
			
			$requirements->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => (($checkDirSerialization == TRUE) ? '<p style="color: green;"><strong>"serialization" dir writeable</strong> - OK</p>' : '<p style="color: red"><strong>"serialization" dir writeable</strong> - No</p>')
			)));
			
			$requirements->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => (($checkOB == TRUE) ? '<p style="color: green;"><strong>"output_buffering" is ON</strong> - OK</p>' : '<p style="color: red"><strong>"output_buffering" is OFF</strong> - No</p>')
			)));
		}
		$this->template->display($this->tplFile, Array(
			'DESIGNPATH' => App::getURLAdress() . 'design/',
			'form' => $form->Render('JS', '', false),
			'error' => $this->error
		));
	}
}

$Installer = new Install();
$Installer->Render();
