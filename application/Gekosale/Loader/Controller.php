<?php 

namespace Gekosale\Loader;

class Controller extends \Gekosale\Loader {
	
	protected $paths;
	
	public function __construct($paths){
		$this->setPaths($paths);
	}
	
}

