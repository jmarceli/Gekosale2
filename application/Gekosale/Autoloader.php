<?php
namespace Gekosale;

class Autoloader
{

	static public function register ()
	{
		ini_set('unserialize_callback_func', 'spl_autoload_call');
		spl_autoload_register(array(
			new self(),
			'autoload'
		));
	}

	static public function autoload ($class)
	{
        $reg = App::getRegistry();
        
        if($reg != null && preg_match("/".preg_quote('Model') .'$/', $class)){
           $modelName = end(explode('\\', strtolower(str_replace('Model', '', $class))));
           $reg->router->modelLoader($modelName, true);
        } else
		if (substr($class, 0, 4) == 'Twig'){
			if (is_file($file = ROOTPATH . 'lib' . DS . 'Twig' . '/../' . str_replace(array(
				'_',
				"\0"
			), array(
				'/',
				''
			), $class) . '.php')){
				require $file;
			}
		}
		else{
			$directories = array(
				__IMAGE_CLASS__,
				__DISPATCHER_CLASS__
			);
			
			$fileNameFormats = array(
				'%s.class.php',
				'%s.php'
			);
			
			$path = $class;
			if (@include_once $path . '.php'){
				return;
			}
			
			$rootPathLen = strlen(ROOTPATH);
			
			foreach ($directories as $directory){
				foreach ($fileNameFormats as $fileNameFormat){
					$path = substr($directory, $rootPathLen) . sprintf($fileNameFormat, $class);
					if (is_file(ROOTPATH . strtolower($path))){
						include_once ROOTPATH . strtolower($path);
						return;
					}
					else 
						if (is_file(ROOTPATH . $path)){
							include_once ROOTPATH . $path;
							return;
						}
				}
			}
		}
	
	}
}
