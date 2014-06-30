<?php

namespace Gekosale;

class LogoutModel extends Component\Model
{

	public function logout ()
	{
		if ($this->registry->router->getAdministrativeMode() == 1){
			Session::flush();
			App::redirectUrl($this->registry->router->generate('frontend.login', true));
		}
		else{
			App::getModel('clientlogin')->destroyAutologinKey();
			Session::flush();
			App::redirectUrl($this->registry->router->generate('frontend.home', true));
		}
	}
}